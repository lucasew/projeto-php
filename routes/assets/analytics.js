

(function(currentScript) {
    <?php require "utils.js"; ?>;
    const eventKey = 'analytics'
    const scriptUrl = new URL(currentScript.src);
    const defaultTag = scriptUrl.searchParams.get("defaultTag") || "generic";
    const host = scriptUrl.searchParams.get("host") || window.location.host;
    window.emitAnalyticsEvent = (tag, payload) => {
        callApi(`api/analytics/ping/${host}/${tag || defaultTag}`, {}, {
            method: 'post',
            body: JSON.stringify(payload)
        })
    }
})(document.currentScript)