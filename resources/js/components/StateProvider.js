import React, {useReducer} from "react";

export const stateContext = React.createContext()

const initialState = {
    orders: [],
    ordersPage: 1,
    ordersPagesTotal: 1,
    ordersHistory: [],
    ordersHistoryPage: 1,
    ordersHistoryPagesTotal: 1,
    user: null,
    currentPrice: 0.0,
}

const stateReducer = (state, action) => {
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
                currentPrice: action.currentPrice
            }}
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
        setCurrentPrice: (currentPrice) => dispatch({
            type: 'setCurrentPrice',
            currentPrice: currentPrice,
        }),
    }
    return (
        <stateContext.Provider value={[state, actions]}>{children}</stateContext.Provider>
    );
}

export default StateProvider;
