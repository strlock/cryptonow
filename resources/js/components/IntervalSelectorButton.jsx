import React from 'react';

const IntervalSelectorButton = ({setChartsInterval, interval, currentInterval, children}) => {
    return (
        <button onClick={() => setChartsInterval(interval)} className={"btn btn-primary btn-sm" + (interval === currentInterval ? ' active' : '')}>{children}</button>
    );
}

export default IntervalSelectorButton;
