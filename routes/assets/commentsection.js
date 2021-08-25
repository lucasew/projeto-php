<?php
header("Content-Type: text/javascript");
?>
// vim:ft=javascript

(async function (currentScript) {
    <?php require "utils.js"; ?>
    const rootElementId = "commentsection"
    const scriptUrl = new URL(currentScript.src);
    const slug = scriptUrl.searchParams.get("slug");
    const host = scriptUrl.searchParams.get("host") || window.location.host;
    console.log(host)

    
   const theNode = currentScript.parentElement.insertBefore(
        createElement({
            type: "div",
            classList: "comment-section",
            id: rootElementId,
            children: []
        }),
        currentScript
    )
    async function rerender() {
        const comments = await callApi(`api/comment/${host}/${slug}/list`)
        if (!comments.ok) {
            const error = {
                404: "Site não cadastrado",
                500: "Erro de servidor"
            }[comments.status]
            theNode.replaceChildren(
                createElement({
                    type: "p",
                    classList: "comment-section-error",
                    children: [
                        createElement({
                            type: "span",
                            children: "ERRO"
                        }),
                        createElement({
                            type: "span",
                            children: error || comments.statusText
                        })
                    ]
                })
            )
            return
        }
        const commentsJson = await comments.json();
        const user = whoami()
        theNode.replaceChildren(
            createElement({
                type: "ul",
                classList: "comment-section-comments",
                id: "comment-section-comments",
                children: commentsJson.result.comments.map((comment) => {
                    const { username, body, cid } = comment
                    return createElement({
                        type: "li",
                        classList: "comment-section-comment",
                        dataset: {
                            username,
                            body,
                            cid
                        },
                        children: [
                            createElement({
                                type: "span",
                                classList: "comment-section-comment-user",
                                children: username
                            }),
                            createElement({
                                type: "span",
                                classList: "comment-section-comment-body",
                                children: body
                            }),
                            ...(!!user ? [
                                createElement({ // css se encarrega de preencher o conteúdo dessa parte
                                    title: "Deletar",
                                    type: "span",
                                    classList: [
                                        "comment-section-comment-button", 
                                        "comment-section-comment-button-delete"
                                    ],
                                    creationHook(e) {
                                        e.style.cursor = "pointer"
                                        e.addEventListener('click', () => {
                                            promiseHandler(callApi(`api/comment/${cid}/delete`)
                                                .then((res) => res.ok && alert("Comentário deletado com sucesso!"))
                                                .then(rerender))
                                        })
                                    }
                                }),
                                createElement({ // css se encarrega de preencher o conteúdo dessa parte
                                    title: "Editar",
                                    type: "span",
                                    classList: [
                                        "comment-section-comment-button",
                                        "comment-section-comment-button-update"
                                    ],
                                    creationHook(e) {
                                        e.style.cursor = "pointer"
                                        e.addEventListener('click', async () => {
                                            const newComment = prompt("Digite o novo texto do comentário", body)
                                            if (!newComment) {
                                                return
                                            }
                                            promiseHandler(callApi(`api/comment/${cid}/update`, {
                                                body: newComment
                                            })
                                                .then((res) => res.ok && alert("Comentário editado com sucesso!"))
                                                .then(rerender))
                                        })
                                    }
                                })
                            ] : [])
                        ]
                    })
                })
            }),
            createElement({
                type: "form",
                classList: ["comment-section-bottom",
                    isLoggedIn()
                        ? "comment-section-bottom-logged"
                        : "comment-section-bottom-unlogged"
                ],
                creationHook(e) {
                    e.addEventListener('submit', (e) => e.preventDefault())
                    if (isLoggedIn()) {
                        e.addEventListener('submit', async (e) => {
                            const body = document.getElementById("comment-section-input").value
                            const res = await callApi(`api/comment/${host}/${slug}/create`, {
                                body
                            });
                            if (res.ok) {
                                alert("Comentário postado com sucesso!")
                                rerender()
                            }
                            return false
                        })
                    }
                },
                children: isLoggedIn() // conferir dps
                    ? [
                        createElement({
                            type: "button",
                            children: user.username,
                            title: "Deslogar",
                            creationHook(e) {
                                e.type = "button"
                                e.addEventListener('click', 
                                    () => handleLogout(rerender))
                            }
                        }),
                        createElement({
                            type: "input",
                            id: "comment-section-input",
                            placeholder: "Insira seu comentário",
                            creationHook(e) {
                                e.type = "text"
                            }
                        }),
                        createElement({
                            type: "button",
                            children: ">",
                            title: "Enviar comentário",
                            creationHook(e) {
                                e.type = "submit"
                            }
                        })
                    ]
                    : [
                        createElement({
                            type: "button",
                            children: "Login",
                            creationHook(e) {
                                e.type = "button"
                                e.addEventListener('click', 
                                    () => handleLogin(rerender))
                            }
                        }),
                        createElement({
                            type: "button",
                            children: "Cadastro",
                            creationHook(e) {
                                e.type = "button"
                                e.addEventListener('click', () => handleCadastro(rerender))
                            }
                        })
                    ]
            })
        )
    }
    rerender()
})(document.currentScript).catch(console.error)
