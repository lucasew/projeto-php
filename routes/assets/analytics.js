

(function(currentScript) {
    <?php require "utils.js"; ?>;
    const scriptUrl = new URL(currentScript.src);
    const defaultTag = scriptUrl.searchParams.get("defaultTag") || "generic";
    const host = scriptUrl.searchParams.get("host") || window.location.host;
    const enablePerformancePacket = scriptUrl.searchParams.get("enablePerformancePacket")
    window.emitAnalyticsEvent = (tag, payload) => {
        callApi(`api/analytics/ping/${host}/${tag || defaultTag}`, {}, {
            method: 'post',
            body: JSON.stringify(payload)
        })
    }
    if (enablePerformancePacket) {
        window.addEventListener('load', () => {
            console.log(`analytics: sending performance packet`)
            emitAnalyticsEvent('performance', window.performance)
        })
    }
})(document.currentScript)