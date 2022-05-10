import React from 'react';
import {ORDER_DIRECTION_TITLES, ORDER_STATE_TITLES} from "../constants";
import FormatHelper from "../Helpers/FormatHelper";
import ReactPaginate from "react-paginate";
import $ from "jquery";
import RequestHelper from "../Helpers/RequestHelper";
import styles from "./OrdersList.module.scss";

function OrdersListTable({orders, page, pagesTotal, showDeleteButton, onPageSelected}) {

    const onDeleteClick = (order) => {
        const $button = $('#order-delete-button-' + order.id);
        $button.addClass('spinner-border');
        RequestHelper.fetch('/api/orders/' + order.id, {
            method: 'DELETE',
        }, () => {
            refresh();
        });
    }

    const table = <table className="table">
        <thead>
        <tr>
            <th className={"text-start"}>Order</th>
            <th className={"text-center"}>Price</th>
            <th className={"text-center"}>Stop Loss</th>
            <th className={"text-center"}>Take Profit</th>
            <th className={"text-center"}>Status</th>
            <th className={"text-center"} width={50}>ID</th>
            <th className={"text-end"} width={50}> </th>
        </tr>
        </thead>
        <tbody>
        {orders.map((order) => {
            const orderClass = 'order order-' + order.state;
            return (
                <tr key={order.id} className={orderClass}>
                    <td className={"text-start order-symbol"}>{ORDER_DIRECTION_TITLES[order.direction]} {FormatHelper.formatAmount(order.amount, true)}</td>
                    <td className={"text-center order-price"}>{FormatHelper.formatPrice(order.price, true)}</td>
                    <td className={"text-center order-sl-tp"}>{FormatHelper.formatPrice(order.sl, true)}</td>
                    <td className={"text-center order-sl-tp"}>{FormatHelper.formatPrice(order.tp, true)}</td>
                    <td className={"text-center order-state"}>{ORDER_STATE_TITLES[order.state]}</td>
                    <td className={"text-center order-symbol"}>{order.id}</td>
                    <td className={"text-end order-actions"}>
                        {showDeleteButton
                            ? <button className="btn btn-danger btn-delete btn-sm" id={'order-delete-button-' + order.id} onClick={() => onDeleteClick(order)}><i className="fa fa-times" aria-hidden="true"> </i></button>
                            : ''
                        }
                    </td>
                </tr>
            );
        })}
        </tbody>
        <tfoot>
            <tr>
                <td colSpan={7}>
                    <div className={styles.paginationContainer}>
                        {pagesTotal > 1 ? (
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
                        ) : ''}
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>;
    return orders.length > 0 ? table : <div className={"text-center"}>No orders</div>;
}

export default OrdersListTable;
