import React, {useContext} from 'react';
import {stateContext} from "./StateProvider";

const IntervalSelectorButton = ({interval, children}) => {
    const [state, actions] = useContext(stateContext);
    return (
        <button onClick={() => actions.setInterval(interval)} className={"btn btn-primary btn-sm" + (interval === state.interval ? ' active' : '')}>{children}</button>
    );
}

export default IntervalSelectorButton;
