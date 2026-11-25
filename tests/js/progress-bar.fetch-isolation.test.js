'use strict'
/**
 * Teste automatizado (Node puro, sem dependências) da barra de progresso de
 * frete grátis (`assets/js/progress-bar.js`).
 *
 * Foco: a barra NÃO pode tratar falha de outro plugin como erro próprio.
 *
 *   1. O interceptor de `window.fetch` devolve a promise ORIGINAL, sem encadear
 *      `.then().catch(rethrow)` — era isso que fazia o erro da Store API
 *      (causado por plugin de terceiro) aparecer com a stack deste arquivo.
 *   2. Falha na requisição do carrinho (HTTP 500, `success:false` ou JSON
 *      inválido) mantém o último estado válido e nunca pinta
 *      "Um erro desconhecido ocorreu".
 *
 * O script é carregado em um DOM fake (vm) com o mínimo que ele usa.
 *
 * Rodar:  node tests/js/progress-bar.fetch-isolation.test.js
 *
 * @package Shipping_Simulator\Tests
 */

const assert = require('assert')
const fs = require('fs')
const path = require('path')
const vm = require('vm')

const SCRIPT_FILE = path.join(__dirname, '..', '..', 'assets', 'js', 'progress-bar.js')
const SCRIPT_SOURCE = fs.readFileSync(SCRIPT_FILE, 'utf8')

const CART_URL = 'https://loja.local/wp-json/wc/store/v1/batch'
const ERROR_TEXT = 'Um erro desconhecido ocorreu'

// ---------------------------------------------------------------------------
// DOM fake mínimo
// ---------------------------------------------------------------------------
function createElement (tagName) {
    const element = {
        tagName: String(tagName || 'div').toUpperCase(),
        className: '',
        id: '',
        style: {},
        dataset: {},
        children: [],
        parentNode: null,
        _text: ''
    }

    Object.defineProperty(element, 'textContent', {
        get: function () { return element._text },
        set: function (value) { element._text = value == null ? '' : String(value) }
    })

    element.classList = {
        add: function (name) {
            const list = element.className.split(/\s+/).filter(Boolean)
            if (list.indexOf(name) === -1) list.push(name)
            element.className = list.join(' ')
        },
        remove: function (name) {
            element.className = element.className
                .split(/\s+/)
                .filter(function (item) { return item && item !== name })
                .join(' ')
        },
        contains: function (name) {
            return element.className.split(/\s+/).indexOf(name) !== -1
        }
    }

    element.appendChild = function (child) {
        child.parentNode = element
        element.children.push(child)
        return child
    }

    element.insertBefore = function (child, reference) {
        child.parentNode = element
        const index = reference ? element.children.indexOf(reference) : -1
        if (index === -1) element.children.push(child)
        else element.children.splice(index, 0, child)
        return child
    }

    element.remove = function () {
        if (!element.parentNode) return
        const siblings = element.parentNode.children
        const index = siblings.indexOf(element)
        if (index !== -1) siblings.splice(index, 1)
        element.parentNode = null
    }

    element.getBoundingClientRect = function () {
        return { height: 20, width: 300, top: 0, left: 0 }
    }

    element.matches = function (selector) {
        if (selector.charAt(0) === '.') return element.classList.contains(selector.slice(1))
        if (selector.charAt(0) === '#') return element.id === selector.slice(1)
        return element.tagName === selector.toUpperCase()
    }

    element.querySelectorAll = function (selector) {
        const out = []
        const walk = function (node) {
            node.children.forEach(function (child) {
                if (child.matches(selector)) out.push(child)
                walk(child)
            })
        }
        walk(element)
        return out
    }

    element.querySelector = function (selector) {
        const found = element.querySelectorAll(selector)
        return found.length ? found[0] : null
    }

    element.addEventListener = function () {}
    element.dispatchEvent = function () { return true }
    element.closest = function () { return null }

    return element
}

function createHarness (options) {
    options = options || {}

    const head = createElement('head')
    const body = createElement('body')
    const roots = [head, body]

    // Alvo real usado pelo script para inserir a barra no DOM. Sem um alvo a
    // barra é criada mas nunca é anexada, e o teste mediria um DOM inexistente.
    const summary = createElement('div')
    const summaryTarget = createElement('div')
    summaryTarget.className = 'woocommerce-checkout-review-order'
    summary.appendChild(summaryTarget)
    body.appendChild(summary)

    const handlers = []

    const document = {
        head: head,
        body: body,
        createElement: createElement,
        getElementById: function (id) {
            let found = null
            const walk = function (node) {
                if (found) return
                if (node.id === id) { found = node; return }
                node.children.forEach(walk)
            }
            roots.forEach(walk)
            return found
        },
        querySelectorAll: function (selector) {
            const out = []
            const walk = function (node) {
                node.children.forEach(function (child) {
                    if (child.matches(selector)) out.push(child)
                    walk(child)
                })
            }
            roots.forEach(walk)
            return out
        },
        querySelector: function (selector) {
            const found = document.querySelectorAll(selector)
            return found.length ? found[0] : null
        },
        addEventListener: function () {},
        dispatchEvent: function () { return true }
    }

    // jQuery fake: `$(document).ready(fn)` roda na hora; `$(document).on(...)`
    // guarda handlers para `trigger()`.
    function $ (target) {
        return {
            ready: function (fn) {
                if (target === document) fn()
                return this
            },
            on: function (events, fn) {
                String(events).split(/\s+/).forEach(function (event) {
                    if (event) handlers.push({ event: event, fn: fn })
                })
                return this
            },
            trigger: function (events) {
                String(events).split(/\s+/).forEach(function (event) {
                    handlers
                        .filter(function (handler) { return handler.event === event })
                        .forEach(function (handler) { handler.fn() })
                })
                return this
            }
        }
    }

    const progressConfig = Object.assign({
        min_free_shipping_value: 100,
        currency_symbol: 'R$',
        min_free_shipping_message: 'Faltam {value} para o frete grátis',
        min_free_shipping_success_message: 'Você ganhou frete grátis!',
        enable_progress_bar_value: 'yes',
        enable_free_shipping_detection: 'yes',
        has_cart_block: true,
        has_checkout_block: false,
        only_digital_products: false,
        ajax_url: 'https://loja.local/wp-admin/admin-ajax.php'
    }, options.config || {})

    return {
        document: document,
        $: $,
        progressConfig: progressConfig,
        trigger: function (event) {
            String(event).split(/\s+/).forEach(function (name) {
                handlers
                    .filter(function (handler) { return handler.event === name })
                    .forEach(function (handler) { handler.fn() })
            })
        }
    }
}

// ---------------------------------------------------------------------------
// Responses fake
// ---------------------------------------------------------------------------
function jsonResponse (body, okFlag) {
    return {
        ok: okFlag !== false,
        status: okFlag === false ? 500 : 200,
        json: function () { return Promise.resolve(body) }
    }
}

function brokenJsonResponse () {
    return {
        ok: true,
        status: 200,
        json: function () { return Promise.reject(new Error('Unexpected token < in JSON')) }
    }
}

function wait (ms) {
    return new Promise(function (resolve) { setTimeout(resolve, ms) })
}

// ---------------------------------------------------------------------------
// Carrega o script em um sandbox isolado
// ---------------------------------------------------------------------------
function loadProgressBar (options) {
    options = options || {}
    const harness = createHarness(options)
    const ajaxCalls = []

    // `fetch` do sandbox = requisição AJAX do nosso backend.
    const ajaxFetch = function (url, config) {
        ajaxCalls.push({ url: url, config: config })
        return options.ajax
            ? options.ajax(url, config)
            : Promise.resolve(jsonResponse({ success: true, data: {} }))
    }

    // `window.fetch` = o que o plugin intercepta (Store API / terceiros).
    const originalFetch = function (url) {
        return options.cartFetch ? options.cartFetch(url) : Promise.resolve(jsonResponse({}))
    }

    const windowObject = {
        location: { hostname: 'loja.local' },
        fetch: originalFetch,
        addEventListener: function () {},
        wcSettings: undefined
    }

    function FakeFormData () {
        const data = []
        this.append = function (key, value) { data.push([key, value]) }
        this.get = function (key) {
            const hit = data.filter(function (item) { return item[0] === key })[0]
            return hit ? hit[1] : null
        }
        this.entries = function () { return data.slice()[Symbol.iterator]() }
    }

    const sandbox = {
        window: windowObject,
        FormData: FakeFormData,
        document: harness.document,
        jQuery: harness.$,
        URL: URL,
        console: console,
        setTimeout: setTimeout,
        clearTimeout: clearTimeout,
        fetch: ajaxFetch,
        wcShippingSimulatorProgress: harness.progressConfig
    }
    sandbox.globalThis = sandbox

    vm.createContext(sandbox)
    vm.runInContext(SCRIPT_SOURCE, sandbox, { filename: 'progress-bar.js' })

    return {
        sandbox: sandbox,
        harness: harness,
        ajaxCalls: ajaxCalls,
        getText: function () {
            const elements = harness.document.querySelectorAll('.wc-shipping-simulator-progress-text')
            return elements.length ? elements[0].textContent : ''
        },
        getInnerText: function () {
            const elements = harness.document.querySelectorAll('.wc-shipping-simulator-progress-inner-text')
            return elements.length ? elements[0].textContent : ''
        }
    }
}

// ---------------------------------------------------------------------------
// Testes
// ---------------------------------------------------------------------------
const tests = []
function test (name, fn) { tests.push({ name: name, fn: fn }) }

test('interceptor devolve a promise original intacta em falha da Store API', async function () {
    const rejected = Promise.reject(new Error('Request failed with status code 500'))

    const loaded = loadProgressBar({ cartFetch: function () { return rejected } })
    const returned = loaded.sandbox.window.fetch(CART_URL, { method: 'POST' })

    assert.strictEqual(returned, rejected, 'o wrapper deve devolver a promise original, sem encadear .then()/.catch()')
    await returned.catch(function () {})
})

test('interceptor não transforma a rejeição de terceiro em erro do plugin', async function () {
    const rejected = Promise.reject(new Error('Request failed with status code 500'))
    const loaded = loadProgressBar({ cartFetch: function () { return rejected } })

    let captured = null
    await loaded.sandbox.window.fetch(CART_URL, {}).catch(function (error) { captured = error })

    assert.ok(captured instanceof Error, 'a rejeição deve chegar ao chamador original')
    assert.strictEqual(captured.message, 'Request failed with status code 500', 'a mensagem original não pode ser embrulhada')
    assert.ok(
        !/progress-bar\.js:\d+/.test(String(captured.stack || '').replace(/progress-bar\.fetch-isolation\.test\.js:\d+/g, '')),
        'a stack não pode ser a deste arquivo: ' + captured.stack
    )
})

test('URL que não é do carrinho passa direto, sem interceptação', async function () {
    const other = Promise.resolve(jsonResponse({}))
    const loaded = loadProgressBar({ cartFetch: function () { return other } })

    assert.strictEqual(loaded.sandbox.window.fetch('https://loja.local/wp-json/wc/store/v1/products', {}), other)
})

test('carrinho de outro domínio passa direto, sem interceptação', async function () {
    const external = Promise.resolve(jsonResponse({}))
    const loaded = loadProgressBar({ cartFetch: function () { return external } })

    assert.strictEqual(loaded.sandbox.window.fetch('https://outro-dominio.com/wp-json/wc/store/v1/batch', {}), external)
})

test('falha da Store API não pinta a barra como erro do plugin', async function () {
    let fail = false
    const loaded = loadProgressBar({
        cartFetch: function () {
            if (fail) return Promise.reject(new Error('Request failed with status code 500'))
            return Promise.resolve(jsonResponse({}))
        },
        ajax: function () {
            return Promise.resolve(jsonResponse({ success: true, data: { cartTotal: 50, freeShipping: false } }))
        }
    })

    fail = true
    await loaded.sandbox.window.fetch(CART_URL, {}).catch(function () {})
    await wait(700)

    assert.ok(loaded.getText().indexOf(ERROR_TEXT) === -1, 'a barra não pode exibir "' + ERROR_TEXT + '": ' + loaded.getText())
})

test('AJAX 500 mantém o último total válido e não mostra erro', async function () {
    let mode = 'ok'
    const loaded = loadProgressBar({
        ajax: function () {
            if (mode === 'ok') {
                return Promise.resolve(jsonResponse({ success: true, data: { cartTotal: 50, freeShipping: false } }))
            }
            return Promise.resolve(jsonResponse({ error: true }, false))
        }
    })

    await wait(700)
    assert.strictEqual(loaded.getText(), 'Faltam R$50.00 para o frete grátis')

    mode = 'fail'
    loaded.harness.trigger('updated_checkout')
    await wait(900)

    assert.ok(loaded.getText().indexOf(ERROR_TEXT) === -1, 'HTTP 500 não vira erro nosso: ' + loaded.getText())
    assert.strictEqual(loaded.getText(), 'Faltam R$50.00 para o frete grátis', 'o último total válido deve ser mantido')
})

test('resposta success:false mantém o estado e nunca pinta erro', async function () {
    const loaded = loadProgressBar({
        ajax: function () {
            return Promise.resolve(jsonResponse({ success: false, data: { error: true } }))
        }
    })

    await wait(700)

    assert.ok(loaded.getText().indexOf(ERROR_TEXT) === -1, 'success:false não vira erro nosso: ' + loaded.getText())
    assert.strictEqual(loaded.getText(), 'Faltam R$100.00 para o frete grátis')
})

test('JSON inválido em resposta 200 é ignorado', async function () {
    const loaded = loadProgressBar({
        ajax: function () { return Promise.resolve(brokenJsonResponse()) }
    })

    await wait(700)

    assert.ok(loaded.getText().indexOf(ERROR_TEXT) === -1, 'JSON inválido não vira erro nosso: ' + loaded.getText())
    assert.strictEqual(loaded.getText(), 'Faltam R$100.00 para o frete grátis')
})

test('carrinho acima do mínimo mostra a mensagem de sucesso', async function () {
    const loaded = loadProgressBar({
        ajax: function () {
            return Promise.resolve(jsonResponse({ success: true, data: { cartTotal: 150, freeShipping: false } }))
        }
    })

    await wait(700)

    assert.strictEqual(loaded.getText(), 'Você ganhou frete grátis!')
    assert.strictEqual(loaded.getInnerText(), 'Completo!')
})

test('o interceptor ainda consulta o carrinho depois de uma requisição bem-sucedida', async function () {
    const loaded = loadProgressBar({
        cartFetch: function () { return Promise.resolve(jsonResponse({})) },
        ajax: function () {
            return Promise.resolve(jsonResponse({ success: true, data: { cartTotal: 50, freeShipping: false } }))
        }
    })

    const before = loaded.ajaxCalls.length
    await loaded.sandbox.window.fetch(CART_URL, {})
    await wait(700)

    assert.ok(loaded.ajaxCalls.length > before, 'a consulta do carrinho deve continuar acontecendo')
    const last = loaded.ajaxCalls[loaded.ajaxCalls.length - 1]
    assert.strictEqual(last.config.body.get('action'), 'wc_shipping_simulator_get_cart_shipping_status')
})

// ---------------------------------------------------------------------------
// Runner
// ---------------------------------------------------------------------------
;(async function () {
    let failed = 0

    for (const item of tests) {
        try {
            await item.fn()
            console.log('ok   - ' + item.name)
        } catch (error) {
            failed++
            console.log('FAIL - ' + item.name)
            console.log('       ' + (error && error.message ? error.message : error))
        }
    }

    console.log('')
    console.log((tests.length - failed) + ' passed, ' + failed + ' failed')
    process.exit(failed === 0 ? 0 : 1)
})()
