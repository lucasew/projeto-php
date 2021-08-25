

(function(currentScript) {
    <?php require "utils.js"; ?>;
    const eventKey = 'analytics'
    const scriptUrl = new URL(currentScript.src);
    const defaultTag = scriptUrl.searchParams.get("defaultTag") || "generic";
    const host = scriptUrl.searchParams.get("host") || window.location.host;
    window.emitAnalyticsEvent = (tag, payload) => {
        const event = new CustomEvent(eventKey, {tag, payload})
        currentScript.dispatchEvent(event)
    }
    currentScript.addEventListener(eventKey, ({tag, payload}) => {
        callApi(`api/analytics/ping/${host}/${tag || defaultTag}`, {}, {
            body: JSON.stringify(payload)
        })
    })
})(document.currentScript)