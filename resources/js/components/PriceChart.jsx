import React from 'react';
import ReactApexChart from 'react-apexcharts';
import RequestHelper from "../Helpers/RequestHelper";
import FormatHelper from "../Helpers/FormatHelper";
import { AdvancedChart } from "react-tradingview-embed";
import CurrentPrice from "./CurrentPrice";

class PriceChart extends React.Component {
    state = {
        series: [{
            name: 'Price',
            data: [],
        }],
        options: {
            chart: {
                type: 'candlestick',
                selection: {
                    enabled: false,
                },
                sparkline: {
                    enabled: false,
                },
                zoom: {
                    enabled: false,
                },
                animations: {
                    enabled: false,
                },
            },
            dataLabels: {
                enabled: false,
            },
            yaxis: {
                title: {
                    text: 'USDT',
                    align: 'center',
                    style: {
                        fontWeight: 'bold',
                        color: this.props.textColor,
                    },
                },
                labels: {
                    formatter: function (y) {
                        return y + ' USDT';
                    },
                    style: {
                        colors: this.props.textColor,
                    }
                },
                forceNiceScale: true,
            },
            xaxis: {
                type: 'datetime',
                categories: [],
                tickAmount: 30,
                tickPlacement: 'on',
                labels: {
                    format: 'HH:mm',
                    rotate: -30,
                    rotateAlways: true,
                    hideOverlappingLabels: false,
                    datetimeUTC: false,
                    minHeight: 50,
                    offsetY: 10,
                    style: {
                        colors: this.props.textColor,
                    },
                    formatter: function (value, timestamp, opts) {
                        return opts.dateFormatter(new Date(timestamp), 'HH:mm')
                    },
                },
                axisTicks: {
                    show: true,
                    borderType: 'solid',
                    color: this.props.linesColor,
                    height: 6,
                    offsetX: 0,
                    offsetY: 0
                },
                tooltip: {
                    enabled: false
                },
                axisBorder: {
                    show: true,
                    color: this.props.linesColor,
                },
            },
            theme: {
                mode: 'dark',
                palette: 'palette2',
            },
            noData: {
                text: "Loading...",
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: this.props.textColor,
                    fontSize: '14px',
                    fontFamily: "Helvetica"
                }
            },
            grid: {
                borderColor: this.props.linesColor,
            },
            annotations: {
                position: 'front',
                yaxis: [
                    {
                        y: 38400,
                        borderColor: '#00E396',
                        label: {
                            borderColor: '#00E396',
                            style: {
                                color: '#fff',
                                background: '#00E396'
                            },
                            text: 'Y-axis annotation on 8800'
                        }
                    }
                ]
            }
        },
    };

    chartContext = null

    constructor(props) {
        super(props);
        let self = this;
        this.state.options.chart.events = {
            mounted: function (chartContext, config) {
                self.chartContext = chartContext;
            }
        };
        this.refresh();
    }

    render() {
        return (
            <div className="card">
                <div className="card-header">Price<CurrentPrice symbol={"BTCBUSD"} /></div>
                <div className="card-body pt-0">
                    <div className="chart">
                        <ReactApexChart options={this.state.options} series={this.state.series} type="candlestick" height={this.props.height} />
                    </div>
                </div>
            </div>
        );
    }

    refresh() {
        let self = this;
        RequestHelper.fetch('/api/price/BTCUSDT/' + this.props.fromTime + '/' + this.props.toTime + '/' + this.props.interval, {},
            response => {
                self.chartContext.updateSeries([{data:response.data}]);
            },
            error => console.log(error)
        );
    }

    componentDidMount() {
        this.refresh();
    }
};

export default PriceChart;
