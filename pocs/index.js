(function () {
    console.log(document.currentScript);
    const url = new URL(document.currentScript.src);
    console.log(url.searchParams.get("eoq"))
})()
