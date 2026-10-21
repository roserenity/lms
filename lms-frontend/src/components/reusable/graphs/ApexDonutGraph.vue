<template>
    <section >
        <section v-if="loading">
            <section class="d-flex flex-row justify-space-between px-2">
                <v-skeleton-loader
                width=80
                type="text"
                class="mb-1"
                ></v-skeleton-loader>

                <v-skeleton-loader
                tile
                width=20
                type="text"
                class="mb-1"
                ></v-skeleton-loader>
            </section>

            <v-skeleton-loader
                tile
                type="image"
                class="px-2"
            ></v-skeleton-loader>
        </section>
        <apexchart v-else :options="{...chartOptions, colors: colors}" :series="series"></apexchart>
    </section>
</template>

<script>

export default {
    props: {
        series: {
            type: Array,
            default: function () {
                return [];
            }
        },
        labels: {
            type: Array,
            default: function () {
                return [];
            }
        },
        title: {
            type: String,
            default: ""
        },
        loading: {
            type: Boolean,
            default: true
        },
        filename: {
            type: String,
            default: ''
        }
    },
    data: () => ({
        //colors:['#CF455C', '#3A3B71', '#ED9265', '#FB6054', '#675293'],
        colors:['#EA8245', '#FFAE2C', '#3A3B71', '#9A2955', '#D33148'],
        chartOptions: {},
    }),
    mounted(){
        this.chartOptions = {
            
            noData: {
                text: "There's no data",
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0
            },
            chart: {
                type: 'donut',
                height: '100%',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                },
                toolbar: {
                    show: true,
                    offsetX: 0,
                    offsetY: 0,
                    tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true | '<img src="/static/icons/reset.png" width="20">',
                    customIcons: []
                    },
                    export: {
                    csv: {
                        filename: this.filename,
                        columnDelimiter: ',',
                        headerCategory: 'category',
                        headerValue: 'count',
                    },
                    svg: {
                        filename: this.filename,
                    },
                    png: {
                        filename: this.filename,
                    }
                    },
                    autoSelected: 'zoom' 
                },
                redrawOnWindowResize: true,
            },
            labels: [],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px',
                    fontWeight: '400',
                    fontFamily:  'Poppins, Arial, sans-serif',
                },
            },
            responsive: [{
                breakpoint: 480,
                options: {
                  chart: {
                    height: 400
                  },
                  legend: {
                    position: 'bottom'
                  }
                }
            }],
            title: {
                text: '',
                style: {
                    fontSize:  '13px',
                    fontWeight:  'bold',
                    fontFamily:  'Poppins, Arial, sans-serif',
                    color:  '#263238'
                },
            },
            plotOptions: {
                pie: {
                    donut: {
                        customScale: 0.8,
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                showAlways: true
                            },
                        }
                    }
                }
            }
        }
        this.chartOptions.labels = this.labels
        this.chartOptions.title.text = this.title
    },
    watch: {
        labels(val) {
            this.chartOptions.labels = val
        }
    }
}

</script>