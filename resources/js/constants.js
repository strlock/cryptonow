const REFRESH_INTERVAL = 15000;
const POPUP_TIMEOUT = 15000;
export const ORDERS_REFRESH_INTERVAL = 3000;
const ONE_MINUTE_MS = 60*1000;

const ORDER_STATE_TITLES = {
    'new': 'New Order',
    'ready': 'Waiting SL or TP.',
    'profit': 'Order completed (profit)',
    'loss': 'Order completed (loss)',
    'failed': 'Order failed',
    'canceled': 'Canceled',
    'completed': 'Completed',
};

const ORDER_DIRECTION_BUY = 'buy'
const ORDER_DIRECTION_SELL = 'sell'

const ORDER_DIRECTION_TITLES = {
    'buy': 'Buy',
    'sell': 'Sell',
};

const ORDERS_LIST_TAB_TITLES = {
    'orders': '',
    'history': 'History',
};

const StrategySignals = {
    BUY: 1,
    SELL: -1,
    NOTHING: 0,
}

export {
    REFRESH_INTERVAL,
    POPUP_TIMEOUT,
    ORDER_STATE_TITLES,
    ORDER_DIRECTION_TITLES,
    ORDERS_LIST_TAB_TITLES,
    ORDER_DIRECTION_BUY,
    ORDER_DIRECTION_SELL,
    StrategySignals,
};
