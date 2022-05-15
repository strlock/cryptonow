import React from 'react';
import {ORDER_DIRECTION_TITLES, ORDER_STATE_TITLES} from "../../constants";
import FormatHelper from "../../Helpers/FormatHelper";
import ReactPaginate from "react-paginate";
import $ from "jquery";
import RequestHelper from "../../Helpers/RequestHelper";
import styles from "./OrdersList.module.scss";
import {ProgressBar} from "react-bootstrap";
import OrderStateHelper from "../../Helpers/OrderStatusHelper";

function OrdersListTable({orders, page, pagesTotal, onPageSelected, onDeleteClick, isHistory}) {
    const table = <table className="table">
        <thead>
        <tr>
            <th className={"text-start"}>Order</th>
            <th className={"text-center"}>Price</th>
            <th className={"text-center"}>Stop Loss</th>
            <th className={"text-center"}>Take Profit</th>
            <th className={"text-center"}>Status</th>
            <th className={"text-center"}>Date</th>
            <th className={"text-center"} width={50}>ID</th>
            <th className={"text-end"} width={50}> </th>
        </tr>
        </thead>
        <tbody>
        {orders.map((order) => {
            const orderClass = 'order order-' + order.state;
            const absDiffPercent = Math.abs(order.diff_percent);
            return (
                <tr key={order.id} className={orderClass}>
                    <td className={"text-start order-symbol"}>{ORDER_DIRECTION_TITLES[order.direction]} {FormatHelper.formatAmount(order.amount, true)}</td>
                    <td className={"text-center order-price"}>{FormatHelper.formatPrice(order.price, true)}</td>
                    <td className={"text-center order-sl-tp"}>{FormatHelper.formatPrice(order.sl, true)}</td>
                    <td className={"text-center order-sl-tp"}>{FormatHelper.formatPrice(order.tp, true)}</td>
                    <td className={"text-center order-state"}>
                        <span className={"order-state-title"}>{ORDER_STATE_TITLES[order.state]}</span>
                        {!isHistory
                            ? ( <span>
                                    <ProgressBar striped={false} now={Math.min(absDiffPercent, 100.0)} label={FormatHelper.formatPercent(absDiffPercent)} variant={order.diff_percent >= 0 ? "success" : "danger"} className={"small-progress"} />
                                    <span className={"order-state-title"}>{ORDER_STATE_TITLES[OrderStateHelper.getNextOrderState(order.state, order.diff_percent)]}</span>
                                </span> ) : null}
                    </td>
                    <td className={"text-center order-symbol"}>{(new Date(order.created_at)).toLocaleString()}</td>
                    <td className={"text-center order-symbol"}>{order.id}</td>
                    <td className={"text-end order-actions"}>
                        {onDeleteClick !== undefined
                            ? <button className="btn btn-danger btn-delete btn-sm" id={'order-delete-button-' + order.id} onClick={() => onDeleteClick(order)}><i className="fa fa-times" aria-hidden="true"> </i></button>
                            : null
                        }
                    </td>
                </tr>
            );
        })}
        </tbody>
        {pagesTotal > 1 ? (
            <tfoot>
                <tr>
                    <td colSpan={7}>
                        <div className={styles.paginationContainer}>
                            <ReactPaginate
                                onPageChange={event => onPageSelected(event.selected + 1)}
                                pageRangeDisplayed={pagesTotal}
                                pageCount={pagesTotal}
                                renderOnZeroPageCount={null}
                                containerClassName={"pagination"}
                                pageClassName={"page-item"}
                                previousClassName={"page-item"}
                                nextClassName={"page-item"}
                                pageLinkClassName={"page-link"}
                                previousLinkClassName={"page-link"}
                                nextLinkClassName={"page-link"}
                                activeClassName={"active"} />
                        </div>
                    </td>
                </tr>
            </tfoot>
        ) : null}
    </table>;
    return orders.length > 0 ? table : <div className={"text-center"}>No orders</div>;
}

export default OrdersListTable;
