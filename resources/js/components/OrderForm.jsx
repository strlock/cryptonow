import React from "react";
import LoginHelper from '../Helpers/LoginHelper';
import RequestHelper from "../Helpers/RequestHelper";

class OrderForm extends React.Component
{
    state = {
        market: false,
    }

    totalRef = null
    amountRef = null

    render()
    {
        let self = this;
        this.onMarketChange = function(event) {
            const target = event.target;
            const value = target.type === 'checkbox' ? target.checked : target.value;
            self.setState({
                market: value
            });
        };
        this.totalRef = React.createRef();
        this.amountRef = React.createRef();
        this.priceRef = React.createRef();
        this.slRef = React.createRef();
        this.tpRef = React.createRef();
        this.marketRef = React.createRef();
        return (
            <div className="card bg-secondary text-white mb-3">
                <div className="card-body">
                    <form>
                        <div className="input-group input-group-sm mb-2">
                            <label htmlFor="price" className="input-group-text w-25">Price</label>
                            <input type="number" name="price" id="price" defaultValue={this.state.market ? this.props.currentPrice : ''} className="form-control" step="0.01" disabled={this.state.market} ref={this.priceRef} onChange={() => this.onPriceChange()} />
                        </div>
                        <div className="input-group input-group-sm mb-2">
                            <label htmlFor="volume" className="input-group-text w-25">Amount</label>
                            <input type="number" name="amount" id="amount" className="form-control" step="0.00001" onChange={() => this.onAmountChange()} ref={this.amountRef} />
                        </div>
                        <div className="input-group input-group-sm mb-2">
                            <label htmlFor="total" className="input-group-text w-25">Total</label>
                            <input type="number" name="total" id="total" className="form-control" step="0.01" onChange={() => this.onTotalChange()} ref={this.totalRef} />
                        </div>
                        <div className="input-group input-group-sm mb-2">
                            <label htmlFor="sl" className="input-group-text w-25">SL</label>
                            <input type="number" name="sl" id="sl" className="form-control" step="0.01" ref={this.slRef} />
                        </div>
                        <div className="input-group input-group-sm mb-2">
                            <label htmlFor="tp" className="input-group-text w-25">TP</label>
                            <input type="number" name="tp" id="tp" className="form-control" step="0.01" ref={this.tpRef} />
                        </div>
                        <div className="form-check form-check-inline mb-3">
                            <input className="form-check-input" type="checkbox" id="inlineCheckbox1" defaultChecked={this.state.market} onClick={this.onMarketChange} ref={this.marketRef} />
                            <label className="form-check-label" htmlFor="inlineCheckbox1">Market</label>
                        </div>
                        <div className="input-group input-group-sm">
                            <button type="button" name="volume" className="btn btn-success form-control" onClick={() => this.onBuyClick()}>BUY</button>
                            <button type="button" name="volume" className="btn btn-danger form-control" onClick={() => this.onSellClick()}>SELL</button>
                        </div>
                    </form>
                </div>
            </div>
        );
    }

    onAmountChange()
    {
        let price = this.getFloatValueByRef(this.priceRef);
        let amount = this.getFloatValueByRef(this.amountRef);
        this.setFloatValueByRef(this.totalRef, price*amount);
    }

    onTotalChange()
    {
        let price = this.getFloatValueByRef(this.priceRef);
        let total = this.getFloatValueByRef(this.totalRef);
        this.setFloatValueByRef(this.amountRef, total/price, 5);
    }

    onPriceChange()
    {
        let price = this.getFloatValueByRef(this.priceRef);
        let amount = this.getFloatValueByRef(this.amountRef);
        this.setFloatValueByRef(this.totalRef, price*amount);
    }

    getFloatValueByRef(ref)
    {
        let result = ref.current.value.replace(/\,+/, '.');
        return !isNaN(result) ? result : 0.0;
    }

    setFloatValueByRef(ref, value, digits)
    {
        if (!digits) {
            digits = 2;
        }
        ref.current.value = value.toFixed(digits);
    }

    onBuyClick()
    {
        this.postOrder('buy');
    }

    onSellClick()
    {
        this.postOrder('sell');
    }

    postOrder(type)
    {
        let self = this;
        let data = new FormData();
        data.append('price', this.priceRef.current.value);
        data.append('amount', this.amountRef.current.value);
        data.append('sl', this.slRef.current.value);
        data.append('tp', this.tpRef.current.value);
        data.append('market', 1*this.marketRef.current.checked);
        data.append('type', type);
        data.append('exchange', 'binance');
        RequestHelper.fetch('/api/orders', {
            method: 'POST',
            body: data,
        }, response => {
            self.clearForm();
            self.props.showPopup('Order created!');
            this.props.ordersList.refresh();
        });
    }

    clearForm()
    {
        this.amountRef.current.value = '';
        this.totalRef.current.value = '';
        this.slRef.current.value = '';
        this.tpRef.current.value = '';
        this.marketRef.current.checked = false;
    }
}

export default OrderForm;
