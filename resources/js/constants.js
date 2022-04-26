const REFRESH_INTERVAL = 15000;
const POPUP_TIMEOUT = 3000;

const ORDER_STATE_TITLES = {
    'new': 'New Order',
    'ready': 'Order is bought/sold',
    'profit': 'Order completed (profit)',
    'loss': 'Order completed (loss)',
    'failed': 'Order failed',
    'canceled': 'Canceled',
    'completed': 'Completed',
};

const ORDER_DIRECTION_TITLES = {
    'buy': 'Buy',
    'sell': 'Sell',
};

const ORDERS_LIST_TAB_TITLES = {
    'active': 'Active',
    'history': 'History',
};

const ORDER_LIST_TAB_ORDER_SATES = {
    'active': ['new', 'ready'],
    'history': ['profit','loss','failed','canceled','completed'],
};

export {
    REFRESH_INTERVAL,
    POPUP_TIMEOUT,
    ORDER_STATE_TITLES,
    ORDER_DIRECTION_TITLES,
    ORDERS_LIST_TAB_TITLES,
    ORDER_LIST_TAB_ORDER_SATES,
};
