const jwtLocalStorageKey = "commentsection-token"
const webserver = '<?php echo $_SERVER["SERVER_NAME"] ?>';

function promiseHandler(promise) {
    return promise.catch((e) => {
        console.error(e)
        alert(`Aconteceu um erro não tratado em um processo em segundo plano. O console tem mais detalhes.`)
    })
}
function getLoginToken() {
    return localStorage.getItem(jwtLocalStorageKey)
}
function isLoggedIn() {
    return !!getLoginToken()
}
async function callApi(url, params = {}, options = {}) {
    try {
        let queryParams = { ...params }
        if (isLoggedIn()) {
            queryParams["jwt"] = getLoginToken()
        }
        // console.log(queryParams)
        const queryParamsStr = new URLSearchParams(queryParams);
        const res = await wrappedFetch(`${url}?${queryParamsStr}`, options)
        return res
    } catch (e) {
        console.error(e)
        alert(`Consulta a API falhou: ${e.message || e}\nO console tem mais detalhes!`)
    }
}

async function jsonCallApi(url, params) {
    const res = await callApi(url, params)
    const json = await res.json()
    console.log(json)
    return json
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

async function handleLogout(callback) {
    if (confirm("Deslogar?")) {
        localStorage.removeItem(jwtLocalStorageKey)
        callback && callback()
    }
}

async function handleLogin(callback) {
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
        callback && callback()
    }
    if (res.status == 401) {
        alert("usuário ou senha inválido")
    }
}

async function handleCadastro(callback) {
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
        callback && callback()
    }
}