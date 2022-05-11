import React, {useReducer} from "react";
import TimeIntervals from "../TimeIntervals";

export const stateContext = React.createContext()

const initialState = {
    orders: [],
    ordersPage: 1,
    ordersPagesTotal: 1,
    ordersHistory: [],
    changedOrderId: 0,
    ordersHistoryPage: 1,
    ordersHistoryPagesTotal: 1,
    user: null,
    currentPrice: 0.0,
    interval: TimeIntervals.FIVE_MINUTES,
    mdClusters: [],
    popup: {
        show: false,
        type: 'success',
        message: '',
        title: '',
    },
}

const stateReducer = (state, action) => {
    console.log('ACTION: ' + action.type, action);
    switch (action.type) {
        case 'setOrders' :
            return {...state, ...{
                orders: action.orders,
                ordersPage: action.page,
                ordersPagesTotal: action.pagesTotal,
            }}
        case 'setOrdersHistory':
            return {...state, ...{
                ordersHistory: action.orders,
                ordersHistoryPage: action.page,
                ordersHistoryPagesTotal: action.pagesTotal,
            }}
        case 'setOrdersPage':
            return {...state, ...{
                ordersPage: action.page
            }}
        case 'setOrdersHistoryPage':
            return {...state, ...{
                ordersHistoryPage: action.page
            }}
        case 'setUser':
            return {...state, ...{
                user: action.user
            }}
        case 'setCurrentPrice':
            return {...state, ...{
                currentPrice: action.price
            }}
        case 'setChangedOrderId':
            return {...state, ...{
                changedOrderId: action.id
            }}
        case 'setInterval':
            return {...state, ...{
                interval: action.interval
            }}
        case 'setMdClusters':
            return {...state, ...{
                mdClusters: action.mdClusters
            }}
        case 'setPopup':
            return {...state, ...{
                popup: action.popup
            }}
        case 'resetPopup':
            return {...state, ...initialState.popup}
        default:
            return state;
    }
}

function StateProvider({children}) {
    const [state, dispatch] = useReducer(stateReducer, initialState);
    const actions = {
        setOrders: (orders, page, pagesTotal) => dispatch({
            type: 'setOrders',
            orders: orders,
            page: page,
            pagesTotal: pagesTotal
        }),
        setOrdersHistory: (orders, page, pagesTotal) => dispatch({
            type: 'setOrdersHistory',
            orders: orders,
            page: page,
            pagesTotal: pagesTotal
        }),
        setOrdersPage: (page) => dispatch({
            type: 'setOrdersPage',
            page: page,
        }),
        setOrdersHistoryPage: (page) => dispatch({
            type: 'setOrdersHistoryPage',
            page: page,
        }),
        setUser: (user) => dispatch({
            type: 'setUser',
            user: user,
        }),
        setCurrentPrice: (price) => dispatch({
            type: 'setCurrentPrice',
            price: price,
        }),
        setChangedOrderId: (id) => dispatch({
            type: 'setChangedOrderId',
            id: id,
        }),
        setInterval: (interval) => dispatch({
            type: 'setInterval',
            interval: interval,
        }),
        setMdClusters: (mdClusters) => dispatch({
            type: 'setMdClusters',
            mdClusters: mdClusters,
        }),
        setPopup: (popup) => dispatch({
            type: 'setPopup',
            popup: popup,
        }),
        resetPopup: () => dispatch({
            type: 'resetPopup',
        }),
    }
    return (
        <stateContext.Provider value={[state, actions]}>{children}</stateContext.Provider>
    );
}

export default StateProvider;
