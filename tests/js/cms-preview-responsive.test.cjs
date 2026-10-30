const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

for (const section of ['home', 'about', 'academics', 'students', 'research', 'events']) {
    test(`${section} preview reflows on phones and tablets and preserves desktop scaling`, () => {
        const source = fs.readFileSync(path.join(__dirname, '../../resources/views/partials', `${section}_cms_preview_editor.blade.php`), 'utf8');
        const body = source.match(/(?:function fit\w+Preview\(frame\)|const fit\w+Preview = \(frame\) =>) \{([\s\S]*?)^ {8}\}/m)[1];
        const properties = {};
        const workspace = { style: { setProperty: (name, value) => { properties[name] = value; } } };
        const shell = { clientWidth: 0 };
        const frame = { closest: selector => selector.endsWith('-workspace') ? workspace : shell };
        let viewportWidth = 0;
        const fit = vm.runInNewContext(`(function(frame) {${body}})`, {
            window: {
                matchMedia: () => ({ matches: viewportWidth <= 1024 }),
                getComputedStyle: () => ({ paddingLeft: '8px', paddingRight: '8px' }),
            },
        });

        // Reuse the same frame to cover resizing between desktop, phone, and tablet.
        for (const [viewport, shellWidth, expectedWidth, expectedScale] of [
            [1440, 1000, 1520, 984 / 1520],
            [393, 361, 345, 1],
            [820, 788, 772, 1],
            [320, 288, 272, 1],
            [1024, 992, 976, 1],
            [1025, 800, 1520, 784 / 1520],
            [1920, 1600, 1520, 1],
        ]) {
            viewportWidth = viewport;
            shell.clientWidth = shellWidth;
            fit(frame);
            assert.equal(properties[`--${section}-preview-width`], `${expectedWidth}px`);
            assert.equal(Number(properties[`--${section}-preview-scale`]), expectedScale);
        }
    });
}

test('phone/tablet wheel gestures scroll cards before the outer CMS page', () => {
    const source = fs.readFileSync(path.join(__dirname, '../../resources/views/partials/cms_preview_compat_assets.blade.php'), 'utf8');
    const body = source.match(/const handleWheel = \(event\) => \{([\s\S]*?)^ {12}\};/m)[1];
    let outerScrolls = 0;
    let compact = true;
    const doc = {
        body: {},
        defaultView: { getComputedStyle: target => target.style },
        scrollingElement: { scrollBy: () => { outerScrolls++; } },
    };
    const wheel = vm.runInNewContext(`(function(event) {${body}})`, {
        doc, document: { scrollingElement: doc.scrollingElement },
        window: { matchMedia: () => ({ matches: compact }) },
    });
    const target = {
        style: { overflowX: 'auto', overflowY: 'hidden' },
        scrollLeft: 0, clientWidth: 300, scrollWidth: 600,
        scrollTop: 0, clientHeight: 200, scrollHeight: 400,
        closest() { return this; }, parentElement: doc.body,
    };
    const gesture = (deltaX, deltaY) => {
        const event = { target, deltaX, deltaY, preventDefault() { this.prevented = true; } };
        wheel(event);
        return !!event.prevented;
    };
    assert.equal(gesture(50, 0), false);
    assert.equal(outerScrolls, 0);
    assert.equal(gesture(0, 50), true);
    target.style.overflowY = 'auto';
    assert.equal(gesture(0, 50), false);
    target.scrollLeft = 300;
    assert.equal(gesture(50, 0), true);
    compact = false;
    target.scrollLeft = 0;
    assert.equal(gesture(50, 0), true);
    assert.equal(outerScrolls, 3);
});
