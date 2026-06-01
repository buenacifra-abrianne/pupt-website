# Botpress Keep-Alive on AWS Free Tier-Friendly Services

This setup keeps a Botpress chatbot awake by invoking an AWS Lambda function every 5 minutes with Amazon EventBridge Scheduler.

If your goal is to reduce Botpress usage and only activate the chatbot when a visitor explicitly opens AI chat, do not deploy this keep-alive stack. The website widget now loads Botpress on first user interaction, so the scheduler is only useful for legacy always-on behavior.

It uses only:

- AWS Lambda
- Amazon EventBridge Scheduler
- CloudWatch Logs
- AWS Budget alert

It does not use EC2, ECS, Fargate, RDS, NAT Gateway, Load Balancer, API Gateway, Route53, or any always-running server.

## Important Placeholder

Your Botpress shareable chatbot URL is:

```text
https://cdn.botpress.cloud/webchat/v3.6/shareable.html?configUrl=https://files.bpcontent.cloud/2026/04/14/09/20260414093100-492RLU02.json
```

Use this as the Lambda environment variable named `BOTPRESS_URL`.

Do not paste an HTML `<script>` tag into Lambda. Paste only the URL.

## Lambda Settings

Use these exact settings:

```text
Function name: botpress-keepalive
Runtime: Node.js 20.x
Architecture: arm64 or x86_64
Memory: 128 MB
Timeout: 10 seconds
VPC: None
Reserved concurrency: 1
Handler: index.handler
Environment variables:
  BOTPRESS_URL = https://cdn.botpress.cloud/webchat/v3.6/shareable.html?configUrl=https://files.bpcontent.cloud/2026/04/14/09/20260414093100-492RLU02.json
  REQUEST_TIMEOUT_MS = 7000
```

Do not attach the function to a VPC. A VPC Lambda often needs a NAT Gateway to reach the internet, and NAT Gateway is not Free Tier-friendly.

## Lambda Code

Use `lambda/index.mjs`.

The function:

- uses built-in `fetch`
- sends a GET request to your Botpress URL
- follows redirects
- times out the HTTP request after 7 seconds
- logs success and failure as small JSON log lines
- throws errors so failed tests/invocations are visible
- uses no dependencies

## AWS Console Setup

### 1. Create Lambda

1. Open the AWS Console.
2. Go to Lambda.
3. Choose Create function.
4. Choose Author from scratch.
5. Function name: `botpress-keepalive`.
6. Runtime: `Node.js 20.x`.
7. Architecture: choose `arm64` if available, otherwise `x86_64`.
8. Permissions: create a new role with basic Lambda permissions, or use the custom IAM policies below.
9. Choose Create function.

### 2. Add Code

1. Open the new Lambda function.
2. Go to the Code tab.
3. Create or rename the file to `index.mjs`.
4. Paste the contents of `lambda/index.mjs`.
5. Deploy.

### 3. Configure Environment Variables

1. Go to Configuration.
2. Go to Environment variables.
3. Choose Edit.
4. Add:

```text
BOTPRESS_URL = https://cdn.botpress.cloud/webchat/v3.6/shareable.html?configUrl=https://files.bpcontent.cloud/2026/04/14/09/20260414093100-492RLU02.json
REQUEST_TIMEOUT_MS = 7000
```

5. Save.

### 4. Configure Low-Cost Runtime Settings

1. Go to Configuration.
2. Go to General configuration.
3. Choose Edit.
4. Set memory to `128 MB`.
5. Set timeout to `10 seconds`.
6. Save.
7. Go to Configuration, then Concurrency.
8. Set reserved concurrency to `1`.

### 5. Confirm No VPC

1. Go to Configuration.
2. Go to VPC.
3. Confirm it says the function is not connected to a VPC.

## IAM Security

Lambda only needs CloudWatch Logs write access.

Use `iam/lambda-trust-policy.json` as the Lambda role trust policy.

Use `iam/lambda-cloudwatch-logs-policy.json` as the Lambda permissions policy.

Before using the policy, replace:

```text
REGION
ACCOUNT_ID
```

Example:

```text
REGION = us-east-1
ACCOUNT_ID = 123456789012
```

If you prefer the beginner console path, AWS may attach this managed policy:

```text
AWSLambdaBasicExecutionRole
```

That policy grants only CloudWatch Logs write permissions, but with broader log resource scope.

## EventBridge Scheduler Setup

Use this exact schedule expression:

```text
rate(5 minutes)
```

Console steps:

1. Open Amazon EventBridge.
2. Go to Scheduler.
3. Choose Schedules.
4. Choose Create schedule.
5. Schedule name: `botpress-keepalive-every-5-minutes`.
6. Schedule pattern: Recurring schedule.
7. Schedule type: Rate-based schedule.
8. Rate expression: `rate(5 minutes)`.
9. Flexible time window: Off.
10. Timeframe: leave start/end date empty unless you want automatic stop dates.
11. Choose Next.
12. Target API: Templated targets.
13. Target service: AWS Lambda.
14. Lambda function: `botpress-keepalive`.
15. Payload: `{}`.
16. Choose Next.
17. Retry policy: disable retry, or set maximum retry attempts to `0`.
18. Dead-letter queue: None.
19. Schedule state: Enabled.
20. Execution role: let Scheduler create a role, or use the Scheduler IAM policy below.
21. Review and create.

Scheduler role:

- Trust policy: `iam/scheduler-trust-policy.json`
- Permission policy: `iam/scheduler-invoke-lambda-policy.json`

Replace `REGION` and `ACCOUNT_ID` before using the policy.

## Manual Lambda Test

1. Open Lambda.
2. Open `botpress-keepalive`.
3. Go to Test.
4. Create a test event named `manual-test`.
5. Use this JSON:

```json
{}
```

6. Choose Test.

Successful result:

```json
{
  "ok": true,
  "statusCode": 200,
  "durationMs": 123
}
```

Any `2xx` status is healthy. A `3xx` usually means the URL redirects; the function follows redirects. A `4xx` or `5xx` means the URL was reached but Botpress or the endpoint rejected/failed the request.

## CloudWatch Logs Monitoring

1. Open CloudWatch.
2. Go to Logs.
3. Go to Log groups.
4. Open `/aws/lambda/botpress-keepalive`.
5. Open the newest log stream.

Success log example:

```json
{
  "level": "info",
  "message": "Botpress keep-alive ping succeeded",
  "status": 200,
  "ok": true,
  "durationMs": 321,
  "target": {
    "protocol": "https:",
    "host": "example.botpress.cloud",
    "pathname": "/..."
  }
}
```

Failure log example:

```json
{
  "level": "error",
  "message": "Botpress keep-alive ping failed",
  "errorName": "AbortError",
  "errorMessage": "This operation was aborted",
  "durationMs": 7005
}
```

Common errors:

- `BOTPRESS_URL is not configured`: environment variable is missing or still the placeholder.
- `AbortError`: Botpress did not respond within `REQUEST_TIMEOUT_MS`.
- `Botpress returned HTTP 404`: the URL is wrong or not publicly reachable.
- `Botpress returned HTTP 500`: Botpress endpoint/server returned an error.

Logs can take several minutes to appear.

## Verify The Schedule Is Running

1. Wait at least 5 to 10 minutes after creating the schedule.
2. Open Lambda.
3. Open `botpress-keepalive`.
4. Check the Monitor tab.
5. Confirm Invocations are increasing.
6. Open CloudWatch log streams and confirm new log entries appear about every 5 minutes.
7. In EventBridge Scheduler, confirm the schedule status is Enabled.

## AWS Budget Alert For $1

1. Open AWS Billing and Cost Management.
2. Go to Budgets.
3. Choose Create budget.
4. Choose Cost budget.
5. Select Monthly budget.
6. Budget name: `one-dollar-safety-budget`.
7. Budget amount: `$1.00`.
8. Scope: All AWS services.
9. Alert threshold: `80%` of budgeted amount, actual cost.
10. Add your email address.
11. Add another alert at `100%`, actual cost.
12. Create budget.
13. Confirm the budget alert email if AWS asks you to.

Use budget alerts only. Do not enable paid budget reports or budget actions unless you understand their pricing.

## Why This Should Stay Near Zero Cost

At one run every 5 minutes, the Lambda runs about 8,640 times in a 30-day month.

This is tiny for Lambda and EventBridge Scheduler Free Tier-friendly usage. The function uses 128 MB memory and normally runs for less than a second. CloudWatch logs are also tiny because each invocation writes only one small success line unless there is an error.

Still, always trust the AWS Billing page and the `$1` budget alert over estimates.

## Services Not To Enable

Do not enable these for this keep-alive task:

- EC2
- ECS
- Fargate
- RDS
- NAT Gateway
- Load Balancer
- API Gateway
- Route53
- VPC attachment for Lambda
- Provisioned Concurrency
- Lambda container images
- CloudWatch custom dashboards or alarms unless you understand pricing
- Paid databases

## Cleanup Checklist

To stop the keep-alive immediately:

1. Open EventBridge Scheduler.
2. Open Schedules.
3. Disable `botpress-keepalive-every-5-minutes`.

To delete everything:

1. Delete the EventBridge schedule.
2. Delete the Lambda function `botpress-keepalive`.
3. Delete the CloudWatch log group `/aws/lambda/botpress-keepalive`.
4. Delete the Scheduler execution role if it is only used for this setup.
5. Delete the Lambda execution role if it is only used for this setup.
6. Keep the `$1` budget alert for safety, or delete it if you no longer want it.

Delete the schedule first. That stops future Lambda invocations.
