import React, {useContext} from "react";
import OrdersListTable from "./OrdersListTable";
import {stateContext} from "./StateProvider";

const OrdersList = () => {
    const [state, actions] = useContext(stateContext);

    const onActivePageSelected = (page) => {
        actions.setOrdersPage(page);
    }

    const onHistoryPageSelected = (page) => {
        actions.setOrdersHistoryPage(page);
    }

    return (
        <div className="table-responsive orders">
            <nav>
                <div className="nav nav-tabs" id="nav-tab" role="tablist">
                    <button className={"nav-link active"} id={"nav-orders-tab"} data-bs-toggle="tab" data-bs-target={"#nav-orders"} type="button" role="tab" aria-controls={"nav-orders"} aria-selected={"true"}>Active Orders</button>
                    <button className={"nav-link"} id={"nav-history-tab"} data-bs-toggle="tab" data-bs-target={"#nav-history"} type="button" role="tab" aria-controls={"nav-history"} aria-selected={"false"}>History</button>
                    <button type="button" className="btn btn-success new-order-button" data-bs-toggle="modal" data-bs-target="#newOrderModal">New order</button>
                </div>
            </nav>
            <div className="tab-content" id="ordersListTabContent">
                <div className={"tab-pane fade active show"} id={"nav-orders"} role="tabpanel">
                    <OrdersListTable orders={state.orders} page={state.ordersPage} pagesTotal={state.ordersPagesTotal} showDeleteButton={true} onPageSelected={page => onActivePageSelected(page)} />
                </div>
                <div className={"tab-pane fade"} id={"nav-history"} role="tabpanel">
                    <OrdersListTable orders={state.ordersHistory} page={state.ordersHistoryPage} pagesTotal={state.ordersHistoryPagesTotal} showDeleteButton={false} onPageSelected={page => onHistoryPageSelected(page)} />
                </div>
            </div>
        </div>
    );
}

export default OrdersList;
