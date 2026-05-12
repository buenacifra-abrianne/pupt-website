const botpressUrl = process.env.BOTPRESS_URL;
const requestTimeoutMs = Number.parseInt(process.env.REQUEST_TIMEOUT_MS || "7000", 10);

function getSafeUrlDetails(url) {
  const parsed = new URL(url);

  return {
    protocol: parsed.protocol,
    host: parsed.host,
    pathname: parsed.pathname
  };
}

export const handler = async () => {
  if (!botpressUrl || botpressUrl === "PASTE_YOUR_BOTPRESS_URL_HERE") {
    throw new Error("BOTPRESS_URL is not configured. Set it in Lambda environment variables.");
  }

  const safeUrlDetails = getSafeUrlDetails(botpressUrl);
  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), requestTimeoutMs);
  const startedAt = Date.now();

  try {
    const response = await fetch(botpressUrl, {
      method: "GET",
      signal: controller.signal,
      redirect: "follow",
      headers: {
        "User-Agent": "botpress-keepalive-lambda/1.0",
        "Cache-Control": "no-cache"
      }
    });

    const durationMs = Date.now() - startedAt;

    console.log(JSON.stringify({
      level: "info",
      message: "Botpress keep-alive ping succeeded",
      status: response.status,
      ok: response.ok,
      durationMs,
      target: safeUrlDetails
    }));

    if (!response.ok) {
      throw new Error(`Botpress returned HTTP ${response.status}`);
    }

    return {
      ok: true,
      statusCode: response.status,
      durationMs
    };
  } catch (error) {
    const durationMs = Date.now() - startedAt;

    console.error(JSON.stringify({
      level: "error",
      message: "Botpress keep-alive ping failed",
      errorName: error.name,
      errorMessage: error.message,
      durationMs,
      target: safeUrlDetails
    }));

    throw error;
  } finally {
    clearTimeout(timeout);
  }
};
