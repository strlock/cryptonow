import React from 'react';
import ReactApexChart from 'react-apexcharts';
import RequestHelper from "../Helpers/RequestHelper";

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
            plotOptions: {
                candlestick: {
                    colors: {
                        upward: 'rgb(26, 121, 26)',
                        downward: 'rgb(172, 19, 19)'
                    }
                }
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
                    },
                },
                labels: {
                    formatter: function (y) {
                        return y + ' USDT';
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
                    formatter: function (value, timestamp, opts) {
                        return opts.dateFormatter(new Date(timestamp), 'HH:mm')
                    },
                },
                axisTicks: {
                    show: true,
                },
                tooltip: {
                    enabled: false
                },
            },
            theme: {
                mode: 'light',
                palette: 'palette3',
            },
            noData: {
                text: "Loading...",
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: "#000000",
                    fontSize: '14px',
                    fontFamily: "Helvetica"
                }
            },
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
            <div className="chart">
                <ReactApexChart options={this.state.options} series={this.state.series} type="candlestick" height={this.props.height} />
            </div>
        );
    }

    refresh() {
        let self = this;
        RequestHelper.fetch('/api/price/BTCUSDT/' + this.props.fromTime + '/' + this.props.toTime + '/' + this.props.interval, {},
            function (response) {
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
