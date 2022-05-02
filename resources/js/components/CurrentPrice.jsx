import React, {useState, useEffect} from 'react';
import FormatHelper from "../Helpers/FormatHelper";
import BinanceWebsocketClient from "./BinanceWebsocketClient";

const CurrentPrice = ({symbol}) => {
    const [currentPrice, setCurrentPrice] = useState(0.0);

    useEffect(() => {
        new BinanceWebsocketClient(function(price) {
            setCurrentPrice(1.0*price);
        }, symbol);
    }, []);

    return (
        <span>{currentPrice !== 0.0 ? ': ' + FormatHelper.formatPrice(currentPrice) : ''}</span>
    );
};

export default CurrentPrice;
