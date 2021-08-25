<?php
header("Content-Type: text/javascript");
?>
// vim:ft=javascript

(async function (currentScript) {
    const jwtLocalStorageKey = "commentsection-token"
    const rootElementId = "commentsection"
    const scriptUrl = new URL(currentScript.src);
    const webserver = '<?php echo $_SERVER["SERVER_NAME"] ?>';
    const slug = scriptUrl.searchParams.get("slug");
    const host = scriptUrl.searchParams.get("host") || window.location.host;
    console.log(host)

    function promiseHandler(promise) {
        return promise.catch((e) => {
            console.error(e)
            alert(`CommentSection: Aconteceu um erro não tratado em um processo em segundo plano. O console tem mais detalhes.`)
        })
    }

    function getLoginToken() {
        return localStorage.getItem(jwtLocalStorageKey)
    }
    function isLoggedIn() {
        return !!getLoginToken()
    }
    async function callApi(url, params = {}) {
        try {
            let queryParams = { ...params }
            if (isLoggedIn()) {
                queryParams["jwt"] = getLoginToken()
            }
            // console.log(queryParams)
            const queryParamsStr = new URLSearchParams(queryParams);
            const res = await wrappedFetch(`${url}?${queryParamsStr}`)
            return res
        } catch (e) {
            console.error(e)
            alert(`Consulta a API falhou: ${e.message || e}\nO console tem mais detalhes!`)
        }
    }
    function wrappedFetch(url, options, ...args) {
        const resolvedURL = `http://${webserver}/${url}`
        // console.log(resolvedURL)
        return fetch(resolvedURL, options, ...args)
    }
    function arrayify(value) {
        return Array.isArray(value) ? value : [value]
    }
    function whoami() {
        if (isLoggedIn()) {
            const jwt = getLoginToken()
            const decoded = JSON.parse(atob(jwt.split(".")[1]))
            return decoded
        }
        return null
    }
    function createElement(params) {
        props = {
            type: "div",
            classList: [],
            children: [],
            dataset: {},
            creationHook: (e) => null,
            ...params
        }
        // console.log(props)
        const { type, classList, children, dataset, creationHook } = props;
        delete props['type']
        delete props['classList']
        delete props['children']
        delete props['dataset']
        delete props['creationHook']
        const elem = document.createElement(type);
        Object.keys(props).forEach((key) => {
            elem[key] = props[key]
        })
        arrayify(classList).forEach((cl) => elem.classList.add(cl))
        arrayify(children).forEach((ch) => typeof ch === 'string' ? elem.innerText = ch : elem.appendChild(ch))
        dataset && Object.keys(dataset).forEach(key => {
            elem.dataset[key] = dataset[key]
        })
        creationHook(elem)
        return elem
    }
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
                                e.addEventListener('click', () => {
                                    if (confirm("Deslogar?")) {
                                        localStorage.removeItem(jwtLocalStorageKey)
                                        rerender()
                                    }
                                })
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
                                e.addEventListener('click', async () => {
                                    const login_user = prompt("Digite seu usuário")
                                    if (!login_user) {
                                        return
                                    }
                                    const login_password = prompt("Digite sua senha")
                                    if (!login_password) {
                                        return
                                    }
                                    const res = await callApi("api/user/login", {
                                        login_user,
                                        login_password
                                    });
                                    if (res.ok) {
                                        const json = await res.json()
                                        localStorage.setItem(jwtLocalStorageKey, json.result.jwt)
                                        alert("login realizado com sucesso")
                                        rerender()
                                    }
                                    if (res.status == 401) {
                                        alert("usuário ou senha inválido")
                                    }
                                })
                            }
                        }),
                        createElement({
                            type: "button",
                            children: "Cadastro",
                            creationHook(e) {
                                e.type = "button"
                                e.addEventListener('click', async () => {
                                    const user = prompt("Digite um usuario")
                                    if (!user) {
                                        return
                                    }
                                    const password = prompt("Digite uma senha")
                                    if (!password) {
                                        return
                                    }
                                    const res = await callApi("api/user/signup", {
                                        user,
                                        password
                                    })
                                    if (res.ok) {
                                        alert("Cadastro realizado com sucesso")
                                        rerender()
                                    }
                                })
                            }
                        })
                    ]
            })
        )
    }
    rerender()
})(document.currentScript).catch(console.error)
