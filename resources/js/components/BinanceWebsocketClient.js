class BinanceWebsocketClient
{
    websocket = null

    constructor(callback, symbol) {
        this.websocket = new WebSocket('wss://stream.binance.com:9443/ws/' + symbol.toLowerCase() + '@miniTicker');
        this.websocket.onmessage = function (event) {
            let data = JSON.parse(event.data);
            callback.call(this, data.c);
        }

    }

    close() {
        this.websocket.close();
    }
}

export default BinanceWebsocketClient;
