<?php
header("Content-Type: text/javascript");
?>

(async function () {
    let elem = document.createElement("div");
    elem.classList.add("comment-section");
    elem.id = "comment-section"
    document.currentScript.parentNode.insertBefore(elem, document.currentScript.nextSibling);
    const webserver = '<?php echo $_SERVER["SERVER_NAME"] ?>';
    const scriptUrl = new URL(document.currentScript.src);
    const slug = scriptUrl.searchParams.get("slug");
    const host = window.location.host;
    const comments = await fetch(`http://${webserver}/api/comment/${host}/${slug}/list`)
    const commentsJson = await comments.json();
    document.getElementById("comment-section").innerHTML = ""
    commentsJson.result.comments.map((comment) => {
        const commElem = document.createElement('p')
        commElem.classList.add("comment-section-comment")
        const username = document.createElement("span")
        username.classList.add("comment-section-comment-username")
        username.innerText = comment.username
        const body = document.createElement("span")
        body.classList.add("comment-section-comment-body")
        body.innerText = comment.body
        commElem.appendChild(username)
        commElem.appendChild(body)
        document.getElementById("comment-section").appendChild(commElem)
        return commElem
    })
    const submitButton = document.createElement("button")
    submitButton.innerText = "Adicionar comentário"
    submitButton.addEventListener('click', () => {
        alert("Você clicou no botão")
    })
    document.getElementById("comment-section").appendChild(submitButton)

})().catch(console.error)