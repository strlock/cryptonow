import React, {useState, useEffect, useContext} from 'react';
import FormatHelper from "../../Helpers/FormatHelper";
import {stateContext} from "../StateProvider";

const CurrentPrice = () => {
    const [state, actions] = useContext(stateContext);
    return (
        <span>{state.currentPrice !== 0.0 ? ': ' + FormatHelper.formatPrice(state.currentPrice) : ''}</span>
    );
};

export default CurrentPrice;
