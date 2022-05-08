import React, {useState, useRef, useContext} from "react";
import RequestHelper from "../Helpers/RequestHelper";
import FormatHelper from "../Helpers/FormatHelper";
import {
    ORDER_DIRECTION_BUY,
    ORDER_DIRECTION_SELL,
} from "../constants";
import currentPriceContext from "../contexts/CurrentPriceContext";

const OrderForm = (props) => {
    const [market, setMarket] = useState(false);
    const [sl, setSl] = useState(0);
    const [tp, setTp] = useState(0);
    const [slPercent, setSlPercent] = useState(0);
    const [tpPercent, setTpPercent] = useState(0);
    const [direction, setDirection] = useState(ORDER_DIRECTION_BUY);

    let currentPrice = useContext(currentPriceContext);

    const totalRef = useRef();
    const amountRef = useRef();
    const priceRef = useRef();
    const slRef = useRef();
    const tpRef = useRef();
    const marketRef = useRef();

    const onMarketChange = (event) => {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        setMarket(value);
    }

    const onAmountChange = () => {
        let price = getFloatValueByRef(priceRef);
        let amount = getFloatValueByRef(amountRef);
        setFloatValueByRef(totalRef, price*amount);
    }

    const onTotalChange = () => {
        let price = getFloatValueByRef(priceRef);
        let total = getFloatValueByRef(totalRef);
        setFloatValueByRef(amountRef, total/price, 5);
    }

    const onPriceChange = () => {
        let price = getFloatValueByRef(priceRef);
        let amount = getFloatValueByRef(amountRef);
        setFloatValueByRef(totalRef, price*amount);
    }

    const getFloatValueByRef = (ref) => {
        let result = ref.current.value.replace(/\,+/, '.');
        return !isNaN(result) ? result : 0.0;
    }

    const setFloatValueByRef = (ref, value, digits) => {
        if (!digits) {
            digits = 2;
        }
        ref.current.value = value.toFixed(digits);
    }

    const onBuyClick = () => {
        postOrder('buy');
    }

    const onSellClick = () => {
        postOrder('sell');
    }

    const postOrder = (direction) => {
        let data = new FormData();
        data.append('price', priceRef.current.value);
        data.append('amount', amountRef.current.value);
        data.append('sl', slRef.current.value);
        data.append('tp', tpRef.current.value);
        data.append('market', 1*marketRef.current.checked);
        data.append('direction', direction);
        data.append('exchange', 'binance');
        data.append('symbol', 'BTCUSD');
        RequestHelper.fetch('/api/orders', {
            method: 'POST',
            body: data,
        }, response => {
            clearForm();
            if (response.error !== undefined) {
                props.showPopup(response.error, 'danger');
                console.log(response.error);
            }
            props.ordersList.refresh();
        });
    }

    const clearForm = () => {
        amountRef.current.value = '';
        totalRef.current.value = '';
        marketRef.current.checked = false;
        setMarket(false);
        setSl(0);
        setTp(0);
        setSlPercent(0);
        setTpPercent(0);
        setDirection(ORDER_DIRECTION_BUY);
    }

    const onSlRangeChange = (event) => {
        const price = getFloatValue(priceRef);
        const value = event.target.value;
        const sl = price*(1-(direction === ORDER_DIRECTION_BUY ? 1 : -1)*value/100);
        setSl(FormatHelper.formatPrice(sl, false, ''));
        setSlPercent(value);
    }

    const onTpRangeChange = (event) => {
        const price = getFloatValue(priceRef);
        const value = event.target.value;
        const tp = price*(1+(direction === ORDER_DIRECTION_BUY ? 1 : -1)*value/100);
        setTp(FormatHelper.formatPrice(tp, false, ''));
        setTpPercent(value);
    }

    const onSlChange = (event) => {
        setSlPercent(0);
        setSl(getFloatValue(slRef));
    }

    const onTpChange = (event) => {
        setTpPercent(0);
        setTp(getFloatValue(tpRef));
    }

    const getFloatValue = (ref) => {
        let value = parseFloat(ref.current.value);
        if (isNaN(value)) {
            value = 0.0;
        }
        return value;
    }

    const onDirectionClick = (direction) => {
        setDirection(direction);
        setSl(0);
        setTp(0);
        setSlPercent(0);
        setTpPercent(0);
    }

    return (
        <div className="card text-white mb-3 order-form">
            <div className="card-body">
                <form>
                    <div className="input-group input-group-sm mb-4 mt-3">
                        <button type="button" name="volume" className="btn btn-success form-control" onClick={() => onDirectionClick(ORDER_DIRECTION_BUY)}>BUY</button>
                        <button type="button" name="volume" className="btn btn-danger form-control" onClick={() => onDirectionClick(ORDER_DIRECTION_SELL)}>SELL</button>
                    </div>
                    <div className="input-group input-group-sm mb-4">
                        <label htmlFor="price" className="input-group-text w-25 bg-dark text-white">Price</label>
                        <input type="text" name="price" id="price" defaultValue={market ? currentPrice : ''} className="form-control bg-dark text-white" disabled={market} ref={priceRef} onChange={() => onPriceChange()} />
                    </div>
                    <div className="input-group input-group-sm mb-4">
                        <label htmlFor="volume" className="input-group-text w-25 bg-dark text-white">Amount</label>
                        <input type="text" name="amount" id="amount" className="form-control bg-dark text-white" onChange={() => onAmountChange()} ref={amountRef} />
                    </div>
                    <div className="input-group input-group-sm mb-4">
                        <label htmlFor="total" className="input-group-text w-25 bg-dark text-white">Total</label>
                        <input type="text" name="total" id="total" className="form-control bg-dark text-white" onChange={() => onTotalChange()} ref={totalRef} />
                    </div>
                    <div className="input-group input-group-sm mb-3">
                        <label htmlFor="tp" className="input-group-text w-25 bg-dark text-white">Take Profit</label>
                        <input type="text" name="tp" id="tp" className="form-control bg-dark text-white" value={tp} onChange={event => onTpChange(event)} ref={tpRef} />
                        <input type="range" className="form-range mt-1" min="0" max="200" step="1" id="tpRange" value={tpPercent} onChange={event => onTpRangeChange(event)} />
                        <span>{direction === ORDER_DIRECTION_BUY ? '+' : '-'}{tpPercent}%</span>
                    </div>
                    <div className="input-group input-group-sm mb-3">
                        <label htmlFor="sl" className="input-group-text w-25 bg-dark text-white">Stop Loss</label>
                        <input type="text" name="sl" id="sl" className="form-control bg-dark text-white" value={sl} onChange={event => onSlChange(event)} ref={slRef} />
                        <input type="range" className="form-range mt-1" min="0" max="50" step="1" id="slRange" value={slPercent} onChange={event => onSlRangeChange(event)} />
                        <span>{direction === ORDER_DIRECTION_BUY ? '-' : '+'}{slPercent}%</span>
                    </div>
                    <div className="form-check form-check-inline mb-4">
                        <input className="form-check-input" type="checkbox" id="marketCheckbox" defaultChecked={market} onClick={onMarketChange} ref={marketRef} />
                        <label className="form-check-label" htmlFor="marketCheckbox">Market</label>
                    </div>
                    <div className="input-group input-group-sm mb-4">
                        {direction === ORDER_DIRECTION_BUY ? (<button type="button" name="order" className="btn btn-success form-control" onClick={() => onBuyClick()}>Place Order</button>) : ''}
                        {direction === ORDER_DIRECTION_SELL ? (<button type="button" name="order" className="btn btn-danger form-control" onClick={() => onSellClick()}>Place Order</button>) : ''}
                    </div>
                </form>
            </div>
        </div>
    );
}

export default OrderForm;
