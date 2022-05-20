import React, {useEffect, useRef, useMemo, useContext} from "react";
import PriceChart from '../../PriceChart/PriceChart';
import TimeHelper from "../../../Helpers/TimeHelper";
import OrderForm from "../../OrderForm/OrderForm";
import BinanceWebsocketClient from "../../BinanceWebsocketClient";
import OrdersList from "../../OrdersList/OrdersList";
import {
    ORDER_DIRECTION_BUY,
    ORDERS_REFRESH_INTERVAL,
    CHARTS_UPDATE_INTERVAL,
} from '../../../constants';
import IntervalSelector from "../../IntervalSelector/IntervalSelector";
import FormatHelper from "../../../Helpers/FormatHelper";
import UserSettingsModal from "../../UserSettings/UserSettingsModal";
import RequestHelper from "../../../Helpers/RequestHelper";
import TimeIntervals from "../../../TimeIntervals";
import Page from '../../Page';
import MarketDeltaChart from '../../MarketDeltaChart/MarketDeltaChart';
import { stateContext } from "../../StateProvider";

const Dashboard = () => {
    const [state, actions] = useContext(stateContext)

    const updateInterval = 15000;
    const priceHeight = 600;
    const mdHeight = 600;
    const chartsTextColor = '#A39ED8';
    const chartsLinesColor = '#635E98';
    const ordersRefreshTimer = useRef(null);
    const wsClient = useRef(null);
    const priceChartRef = useRef();
    const mdChartRef = useRef();
    const ordersListRef = useRef();
    const updateTimeRangeInterval = useRef(null);

    const isLoggedIn = () => state.user !== null;

    const daysForInterval = TimeHelper.daysForInterval(state.interval)
    /*if (daysForInterval > 10) {
        daysForInterval = 10;
    }*/

    const updateTimeRange = () => {
        const fromTime = TimeHelper.round((TimeHelper.subDaysFromDate(new Date(), daysForInterval)).getTime(), state.interval);
        const toTime = TimeHelper.round((new Date()).getTime(), state.interval);
        actions.setTimeRange(fromTime, toTime);
    }

    useEffect(() => {
        if (isLoggedIn()) {
            updateTimeRange();
            updateTimeRangeInterval.current = setInterval(() => {
                updateTimeRange();
            }, CHARTS_UPDATE_INTERVAL);
        } else if (updateTimeRangeInterval.current !== null) {
            clearInterval(updateTimeRangeInterval.current);
            updateTimeRangeInterval.current = null;
        }
        return () => {
            clearInterval(updateTimeRangeInterval.current);
            updateTimeRangeInterval.current = null;
        }
    }, [state.user]);

    useEffect(() => {
        if (isLoggedIn()) {
            wsClient.current = new BinanceWebsocketClient(function(price) {
                actions.setCurrentPrice(1.0*price);
            }, 'BTCBUSD');
        } else if (wsClient.current !== null) {
            wsClient.current.close();
            wsClient.current = null;
        }
        return null;
    }, [state.user]);

    useEffect(() => {
        if (isLoggedIn()) {
            RequestHelper.fetch('/api/mdclusters/BTCUSD', {}, response => {
                actions.setMdClusters(response.data);
            });
        }
    }, [state.interval, state.user]);

    const mdClustersAnnotations = useMemo(() => {
        const annotations = [];
        if (state.mdClusters === undefined || state.mdClusters.length === 0) {
            return [];
        }
        state.mdClusters.forEach((mdCluster, i) => {
            const borderColor = i !== 0 ? chartsLinesColor : '#00ff00';
            const relativePriceDiffPercent = 100*(mdCluster.toPrice-mdCluster.fromPrice)/mdCluster.fromPrice;
            const opacity = i === 0 ? 0.7 : 0.3;
            let annotation = {
                x: Math.round(mdCluster.fromTime - TimeIntervals.FIVE_MINUTES / 2),
                x2: Math.round(mdCluster.toTime - TimeIntervals.FIVE_MINUTES / 2),
                strokeDashArray: 0,
                borderColor: borderColor,
                fillColor: '#244B4B',
                opacity: opacity,
            };
            if (i === 0) {
                annotation.label = {
                    text: FormatHelper.formatAmount(mdCluster.marketDelta) + ', ' + (Math.round(relativePriceDiffPercent*100)/100) + '%',
                    borderColor: chartsLinesColor,
                    style: {
                        color: chartsTextColor,
                        background: 'transparent',
                        opacity: opacity,
                    },
                };
            }
            annotations.push(annotation);
        });
        return annotations;
    }, [state.mdClusters]);

    const getToTimeAnnotation = () => {
        return {
            x: Math.round(state.toTime - state.interval / 2),
            x2: null,
            strokeDashArray: 0,
            borderColor: '#00ff00',
            label: {
                text: (new Date(state.toTime)).toLocaleTimeString(),
                borderColor: chartsLinesColor,
                style: {
                    color: chartsTextColor,
                    background: 'transparent'
                },
            }
        };
    }

    const orderAnnotations = useMemo(() => {
        const result = [];
        const buyColor = '#00E396';
        const sellColor = '#E30096';
        for(let i in state.orders) {
            const order = state.orders[i];
            result.push({
                y: order.price,
                borderColor: order.direction === ORDER_DIRECTION_BUY ? buyColor : sellColor,
                strokeDashArray: 0,
                label: {
                    borderColor: chartsLinesColor,
                    style: {
                        color: chartsTextColor,
                        background: 'transparent'
                    },
                    text: 'Order ' + order.id + ': ' + FormatHelper.formatPrice(order.price),
                    textAnchor: 'start',
                    position: 'left',
                }
            });
            if (order.sl) {
                result.push({
                    y: order.sl,
                    borderColor: order.direction === ORDER_DIRECTION_BUY ? buyColor : sellColor,
                    strokeDashArray: 5,
                    label: {
                        borderColor: chartsLinesColor,
                        style: {
                            color: chartsTextColor,
                            background: 'transparent'
                        },
                        text: 'Order ' + order.id + ' SL: ' + FormatHelper.formatPrice(order.sl),
                        textAnchor: 'start',
                        position: 'left',
                    }
                });
            }
            if (order.tp) {
                result.push({
                    y: order.tp,
                    borderColor: order.direction === ORDER_DIRECTION_BUY ? buyColor : sellColor,
                    strokeDashArray: 5,
                    label: {
                        borderColor: chartsLinesColor,
                        style: {
                            color: chartsTextColor,
                            background: 'transparent'
                        },
                        text: 'Order ' + order.id + ' TP: ' + FormatHelper.formatPrice(order.tp),
                        textAnchor: 'start',
                        position: 'left',
                    }
                });
            }
        }
        return result;
    }, [state.orders]);

    useEffect(() => {
        if (isLoggedIn()) {
            RequestHelper.fetch('/api/orders?page=' + state.ordersPage, {}, response => {
                actions.setOrders(response.data, response.meta.current_page, response.meta.last_page);
            });
        }
    }, [state.ordersPage, state.ordersPagesTotal, state.ordersReRender, state.user]);

    useEffect(() => {
        if (isLoggedIn()) {
            RequestHelper.fetch('/api/orders?history=1&page=' + state.ordersHistoryPage, {}, response => {
                actions.setOrdersHistory(response.data, response.meta.current_page, response.meta.last_page);
            });
        }
    }, [state.ordersHistoryPage, state.ordersHistoryPagesTotal, state.ordersReRender, state.user]);

    useEffect(() => {
        if (isLoggedIn()) {
            ordersRefreshTimer.current = setInterval(() => {
                actions.ordersReRender();
            }, ORDERS_REFRESH_INTERVAL);
        } else if (ordersRefreshTimer.current !== null) {
            clearInterval(ordersRefreshTimer.current);
            ordersRefreshTimer.current = null;
        }
    }, [state.user]);

    const annotations = [...mdClustersAnnotations, getToTimeAnnotation()];
    return (
        <Page>
            <div className="card">
                <div className={"card-header"}>
                    <IntervalSelector />
                </div>
                <div className="card-body pt-0">
                    <div className="chart">
                        <PriceChart
                            fromTime={state.fromTime}
                            toTime={state.toTime}
                            interval={state.interval}
                            height={priceHeight}
                            textColor={chartsTextColor}
                            linesColor={chartsLinesColor}
                            innerRef={priceChartRef}
                            xAnnotations={annotations}
                            yAnnotations={orderAnnotations}
                            orders={state.orders}
                        />
                        <MarketDeltaChart
                            fromTime={state.fromTime}
                            toTime={state.toTime}
                            interval={state.interval}
                            height={mdHeight}
                            updateInterval={updateInterval}
                            textColor={chartsTextColor}
                            linesColor={chartsLinesColor}
                            innerRef={mdChartRef}
                            xAnnotations={annotations} />
                    </div>
                </div>
                <br/>
                <div className="card">
                    <div className="card-body">
                        <OrdersList
                            innerRef={ordersListRef}
                            orders={state.orders}
                            ordersHistory={state.ordersHistory}
                            ordersPage={state.ordersPage}
                            ordersHistoryPage={state.ordersHistoryPage}
                            ordersPagesTotal={state.ordersPagesTotal}
                            ordersHistoryPagesTotal={state.ordersHistoryPagesTotal}
                        />
                    </div>
                </div>
            </div>
            <OrderForm />
        </Page>
    )
}

export default Dashboard;
