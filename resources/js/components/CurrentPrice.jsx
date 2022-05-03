import React, {useState, useEffect, useContext} from 'react';
import FormatHelper from "../Helpers/FormatHelper";
import currentPriceContext from "../contexts/CurrentPriceContext";

const CurrentPrice = () => {
    let currentPrice = useContext(currentPriceContext);
    return (
        <span>{currentPrice !== 0.0 ? ': ' + FormatHelper.formatPrice(currentPrice) : ''}</span>
    );
};

export default CurrentPrice;
