class BinanceWebsocketClient
{
    websocket = null

    constructor(callback) {
        this.websocket = new WebSocket('wss://stream.binance.com:9443/ws/btcusdt@miniTicker');
        this.websocket.onmessage = function (event) {
            let data = JSON.parse(event.data);
            callback.call(this, data.c);
        }

    }
}

export default BinanceWebsocketClient;
