;(() => {

    window.__OYST__ = window.__OYST__ || {
            getOneClickURL: (err, cb) => {
                onOrderFail('OYST 1Click | Method `getOneClickURL` is not defined....')
            }
        };

    const config = {
        button: {
            src: 'https://cdn.staging.uptain.eu/1click/button/index.html',
            id: 'oyst-1click-button',
            width: 230,
            height: 50
        },
        modal: {
            id: 'oyst-1click-modal',
            width: 500,
            height: 700
        },
        modalWrapper: {
            id: 'oyst-1click-modal-wrapper'
        },
        apiURL: 'http://user-bo-front-integration.herokuapp.com'
    };

    const createIframe = options => {
        const { src, id, width, height } = options
        const iframe = document.createElement('iframe')
        iframe.src = src
        iframe.frameBorder = "0"
        iframe.scrolling = "no"
        iframe.width = width
        iframe.height = height
        iframe.id = id
        return iframe
    };

    // Iframes
    const buttonIframe = createIframe(config.button)

    // Simulate getOneClickURL (normally created by the merchant plugin)
    // window.__OYST__.getOneClickURL = cb => cb(null, 'https://oneclick-order-integration.herokuapp.com?m=123&t=456')

    const onOrderFail = err => {
        document.getElementById(config.button.id).contentWindow.postMessage({ type: 'ORDER_REQUEST_FAILED' }, '*')
        console.error(err.toString())
    };

    const injectButton = () => {
        const buttonContainer = document.getElementById(config.button.id)
        if (!buttonContainer) {
            return false
        }
        const parentNode = buttonContainer.parentNode;
        parentNode.replaceChild(buttonIframe, buttonContainer);
        return buttonIframe
    };

    const injectModalWrapper = () => {
        const wrapper = document.createElement('div')
        wrapper.id = config.modalWrapper.id
        wrapper.style.display = 'none'
        wrapper.style.alignItems = 'center'
        wrapper.style.justifyContent = 'center'
        wrapper.style.position = 'fixed'
        wrapper.style.top = '0'
        wrapper.style.left = '0'
        wrapper.style.backgroundColor = 'rgba(0,0,0,0.7)'
        wrapper.style.width = '100%'
        wrapper.style.height = '100vh'
        document.getElementsByTagName('body')[0].appendChild(wrapper)
    };

    const openModal = (err, src) => {
        if (err) {
            return onOrderFail(err)
        }
        const modalWrapper = document.getElementById(config.modalWrapper.id)
        const modalIframe = createIframe(config.modal)
        modalIframe.style.backgroundColor = '#FFF'
        modalIframe.style.borderRadius = '4px'
        modalIframe.src = src
        modalWrapper.innerHTML = ''
        modalWrapper.appendChild(modalIframe)
        modalWrapper.style.display = 'flex'
        document.getElementById(config.button.id).contentWindow.postMessage({ type: 'ORDER_REQUESTED' }, '*')
    };

    const listenToIframeMessages = () => {
        window.addEventListener('message',event => {
            switch (event.data.type) {
                case 'ORDER_REQUEST':
                    try {
                        window.__OYST__.getOneClickURL(openModal)
                    } catch (err) {
                        onOrderFail(err)
                    }
                    break
                case 'MODAL_CLOSE':
                    document.getElementById(config.modalWrapper.id).style.display = 'none'
                    break
            }
        }, false)
    };

    const getUserStatus = () => {
        const req = new XMLHttpRequest()
        req.open('GET', `${config.apiURL}/user`, false)
        req.setRequestHeader("X-Foo", "Bar")
        req.send(null)
        return req.status
    }

    window.onload = () => {
        // const userStatus = getUserStatus()
        const userStatus = 200
        if (userStatus !== 200) {
            return
        }
        var button = injectButton();
        if (!button) {
            return
        }
        injectModalWrapper()
        listenToIframeMessages()
    };
})();
