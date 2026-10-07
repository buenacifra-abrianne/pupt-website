import importlib.util
import sys
from pathlib import Path
from unittest.mock import Mock

import pytest

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT))
from botpress_client import BotpressClient
from compute_f1 import summarize
from knowledge import KnowledgeIndex

spec = importlib.util.spec_from_file_location('evaluation', ROOT / 'test_botpress.py')
evaluation = importlib.util.module_from_spec(spec)
spec.loader.exec_module(evaluation)


def client():
    result = object.__new__(BotpressClient)
    result._get = Mock()
    return result


def test_pagination_and_repeated_tokens():
    c = client()
    c._get.side_effect = [
        {'files': [{'id': 'a'}], 'meta': {'nextToken': 'next'}},
        {'files': [{'id': 'b'}], 'meta': {}},
    ]
    assert [f['id'] for f in c._pages('/files', 'files')] == ['a', 'b']
    assert c._get.call_args.args[1]['nextToken'] == 'next'
    c._get.side_effect = [{'files': [], 'meta': {'nextToken': 'x'}}] * 2
    with pytest.raises(RuntimeError, match='repeated'):
        c._pages('/files', 'files')


def test_all_kbs_and_all_passage_pages():
    c = client()
    c._get.side_effect = [
        {'files': [{'id': 'pdf', 'status': 'indexing_completed', 'tags': {'kbId': 'a'}}], 'meta': {'nextToken': 'f2'}},
        {'files': [{'id': 'web', 'status': 'indexing_completed', 'tags': {'kbId': 'b'}}], 'meta': {}},
        {'passages': [{'id': 'p1', 'content': 'First page'}], 'meta': {'nextToken': 'p2'}},
        {'passages': [{'id': 'p2', 'content': 'Last page'}], 'meta': {}},
        {'passages': [{'id': 'p3', 'content': 'Website'}], 'meta': {}},
    ]
    passages = c.fetch_knowledge_base()
    assert len(passages) == 3
    assert {p['kb_id'] for p in passages} == {'a', 'b'}


@pytest.mark.parametrize('status,content', [('indexing_failed', 'text'), ('indexing_completed', '')])
def test_incomplete_kb_fails_instead_of_skipping(status, content):
    c = client()
    c._get.side_effect = [
        {'files': [{'id': 'pdf', 'status': status}], 'meta': {}},
        {'passages': [{'id': 'p', 'content': content}], 'meta': {}},
    ]
    with pytest.raises(RuntimeError):
        c.fetch_knowledge_base()


def message(id, direction, text, time):
    return {'id': id, 'direction': direction, 'payload': {'text': text}, 'createdAt': time}


def test_full_answer_and_first_response_latency_and_latest_order():
    c = client()
    c.fetch_conversations = Mock(return_value=[{'id': 'c'}])
    c.fetch_messages = Mock(return_value=[
        message('b2', 'outgoing', 'second part', '2026-10-07T01:00:04Z'),
        message('u', 'incoming', 'question', '2026-10-07T01:00:00Z'),
        message('b1', 'outgoing', 'first part', '2026-10-07T01:00:02Z'),
        message('ignored', None, 'unknown direction', '2026-10-07T01:00:03Z'),
        message('u2', 'incoming', 'next', '2026-10-07T01:01:00Z'),
        message('b3', 'outgoing', 'latest', '2026-10-07T01:01:03Z'),
    ])
    pairs = c.get_qa_pairs()
    assert pairs[0]['output'] == 'latest'
    assert pairs[1]['output'] == 'first part\nsecond part'
    assert pairs[1]['response_latency'] == 2
    assert c._timestamp('invalid') is None
    assert c._timestamp('2026-10-07T01:00:00') is None


def test_log_fetch_stops_when_enough_answered_turns_found():
    c = client()
    c.fetch_conversations = Mock(return_value=[{'id': 'recent'}, {'id': 'older'}])
    c.fetch_messages = Mock(return_value=[
        message('u', 'incoming', 'question', '2026-10-07T01:00:00Z'),
        message('b', 'outgoing', 'Actual answer.', '2026-10-07T01:00:02Z'),
    ])
    assert len(c.get_qa_pairs(limit_pairs=1)) == 1
    c.fetch_messages.assert_called_once_with('recent')


def test_retrieval_reaches_end_of_pdf():
    passage = {'id': 'p', 'source': 'PDF', 'page': 40, 'content': 'noise ' * 2000 + 'Scholarship deadline is October 30.'}
    context, count = KnowledgeIndex([passage]).retrieve('Scholarship deadline?', '', 6000)
    assert 'Scholarship deadline is October 30.' in context
    assert 'page: 40' in context
    assert count > 0
    assert len(context) <= 6000


def test_evaluator_retries_invalid_json_without_fabricating_scores(monkeypatch):
    monkeypatch.setenv("EVALUATION_ATTEMPTS", "2")
    response = Mock()
    response.json.return_value = {'response': 'not JSON'}
    post = Mock(return_value=response)
    monkeypatch.setattr(evaluation.requests, 'post', post)
    index = KnowledgeIndex([{'id': 'p', 'source': 's', 'page': 1, 'content': 'Tuition is free.'}])
    with pytest.raises(ValueError):
        evaluation.evaluate_pair({'input': 'Tuition?', 'output': 'Free tuition.'}, index)
    assert post.call_count == 2


def test_new_run_archives_reports_after_preflight(monkeypatch, tmp_path):
    report = tmp_path / 'evaluation_report.csv'
    report.write_text('old evaluation', encoding='utf-8')
    (tmp_path / 'f1_metrics.csv').write_text('old summary', encoding='utf-8')
    monkeypatch.setattr(evaluation, 'ROOT', tmp_path)
    monkeypatch.setattr(evaluation, 'REPORT_FILE', report)
    c = Mock()
    c.fetch_knowledge_base.return_value = [{'id': 'p', 'file_id': 'f', 'kb_id': 'kb',
                                          'source': 'pdf', 'page': 1, 'content': 'Tuition is free.'}]
    monkeypatch.setattr(evaluation, 'load_pdf_knowledge', c.fetch_knowledge_base)
    response = Mock()
    response.json.return_value = {'models': [{'name': evaluation.OLLAMA_MODEL}]}
    monkeypatch.setattr(evaluation.requests, 'get', lambda *a, **k: response)
    evaluation.evaluation_run.__wrapped__(request=None)
    assert 'old evaluation' not in report.read_text()
    assert list((tmp_path / 'report_history').glob('*/evaluation_report.csv'))[0].read_text() == 'old evaluation'
    assert list((tmp_path / 'report_history').glob('*/f1_metrics.csv'))[0].read_text() == 'old summary'


def test_preflight_failure_preserves_reports(monkeypatch, tmp_path):
    report = tmp_path / 'evaluation_report.csv'
    report.write_text('old evaluation', encoding='utf-8')
    monkeypatch.setattr(evaluation, 'ROOT', tmp_path)
    monkeypatch.setattr(evaluation, 'REPORT_FILE', report)
    c = Mock()
    c.fetch_knowledge_base.side_effect = RuntimeError('Indexing incomplete')
    monkeypatch.setattr(evaluation, 'load_pdf_knowledge', c.fetch_knowledge_base)
    with pytest.raises(RuntimeError):
        evaluation.evaluation_run.__wrapped__(request=None)
    assert report.read_text() == 'old evaluation'


@pytest.mark.parametrize('text', ["I couldn't help you with that", 'I cannot answer that', 'Hindi ko alam', "I'm unable to assist"])
def test_refusal_patterns(text):
    assert evaluation.refusal_phrase(text)


def judgment(answerable, refusal, score=9):
    return dict(answerable=answerable, refusal=refusal, relevancy=score, accuracy=score, faithfulness=score)


def test_answer_counts_include_false_positives():
    assert evaluation.outcome(judgment(True, False)) == dict(TP=1, FN=0, FP=0, TN=0)
    assert evaluation.outcome(judgment(True, True)) == dict(TP=0, FN=1, FP=0, TN=0)
    assert evaluation.outcome(judgment(False, False)) == dict(TP=0, FN=0, FP=1, TN=0)
    assert evaluation.outcome(judgment(False, True)) == dict(TP=0, FN=0, FP=0, TN=1)
    assert evaluation.outcome(judgment(True, False, 2)) == dict(TP=0, FN=1, FP=1, TN=0)


@pytest.mark.parametrize('text', ['', "I couldn't help you with that", 'Token limit exceeded',
                                 'You have reached your token limit.', 'Maximum context length exceeded'])
def test_unanswered_turns_excluded(text):
    assert not BotpressClient.is_answer(text)


def test_real_answers_retained():
    assert BotpressClient.is_answer('Apply through the admissions portal.')


def test_compact_judgment_requires_only_scores_and_flags():
    data = judgment(True, False)
    assert evaluation.validate_judgment(data, 'KB text', 1) == data
    data['accuracy'] = True
    with pytest.raises(ValueError, match='score'):
        evaluation.validate_judgment(data, 'KB text', 1)
    with pytest.raises(ValueError, match='JSON object'):
        evaluation.validate_judgment([], '', 0)


def test_feedback_and_missing_information_filtered():
    assert not BotpressClient.is_answer('Did I answer your question correctly?')
    assert not BotpressClient.is_answer("I couldn't find that information on the PUP-T website. Please contact admissions.")
    assert BotpressClient.is_answer('Apply through the admissions portal.')


def test_summary_all_metrics_errors_and_undefined_f1():
    rows = []
    for answerable, refusal, score in [(True, False, 9), (True, True, 2), (False, False, 2), (False, True, 9)]:
        row = {'Evaluation Status': 'OK', 'Unsupported Answer': 'YES' if refusal else 'NO',
               'Overall Pass': 'PASS' if score == 9 else 'FAIL', 'Response Latency (seconds)': '2', 'Latency Pass': 'PASS'}
        row.update(evaluation.outcome(judgment(answerable, refusal, score)))
        for metric in ('Relevancy', 'Accuracy', 'Faithfulness'):
            row[f'{metric} Score (0-10)'] = score
            row[f'{metric} Pass'] = 'PASS' if score == 9 else 'FAIL'
        rows.append(row)
    rows.append({'Evaluation Status': 'ERROR', 'Overall Pass': 'ERROR', 'Response Latency (seconds)': 'N/A'})
    summary = summarize(rows)
    assert summary['Total Cases'] == 5
    assert summary['Scored Cases'] == 4
    assert summary['False Positive'] == 1
    assert summary['Precision'] == summary['Recall'] == summary['F1 Score'] == .5
    assert summary['Unsupported Answers (refusals)'] == 2
    assert summary['Pass Rate (%)'] == 40
    assert summary['Accuracy (avg 0-10)'] == 5.5
    assert not any('Latency' in key for key in summary)
    assert summarize([])['F1 Score'] == 'N/A'
    assert summarize([{'Relevancy Pass': 'PASS'}])['Legacy / Unscored Cases'] == 1


def test_pdf_cache_reused_and_invalidated(monkeypatch, tmp_path):
    import local_knowledge
    folder = tmp_path / 'pdfs'
    folder.mkdir()
    pdf = folder / 'source.pdf'
    pdf.write_bytes(b'first version')
    page = Mock()
    page.extract_text.return_value = 'Tuition is free.'
    reader = Mock(return_value=Mock(pages=[page]))
    monkeypatch.setattr(local_knowledge, 'PdfReader', reader)
    cache = tmp_path / 'cache.json'
    first = local_knowledge.load_pdf_knowledge(folder, cache)
    assert first[0]['page'] == 1
    assert local_knowledge.load_pdf_knowledge(folder, cache) == first
    assert reader.call_count == 1
    pdf.write_bytes(b'second version')
    local_knowledge.load_pdf_knowledge(folder, cache)
    assert reader.call_count == 2


def test_missing_and_scanned_pdfs_fail_explicitly(monkeypatch, tmp_path):
    import local_knowledge
    with pytest.raises(ValueError, match='No local PDFs'):
        local_knowledge.load_pdf_knowledge(tmp_path, tmp_path / 'cache.json')
    (tmp_path / 'scan.pdf').write_bytes(b'scan')
    page = Mock()
    page.extract_text.return_value = ''
    monkeypatch.setattr(local_knowledge, 'PdfReader', lambda *a: Mock(pages=[page]))
    with pytest.raises(ValueError, match='OCR'):
        local_knowledge.load_pdf_knowledge(tmp_path, tmp_path / 'cache.json')
