<template>
    <div>
      <canvas ref="chart"  style="width: 300px;"></canvas>
    </div>
</template>

<script>
import Chart from 'chart.js/auto';
import { Title,
  Tooltip,
  Legend, BarController, CategoryScale, LinearScale} from 'chart.js';

export default {
    name: 'HorizontalBarChart',
    props: {
        chartData: {
            type: Object,
            required: true
        }
    },
    data: () => ({
        ctx: null
    }),
    mounted () {
        Chart.register(BarController, CategoryScale, LinearScale, Title, Tooltip, Legend);

        this.renderChart();
    },
    methods: {
        renderChart () {
            let _ctx = this.$refs.chart.getContext('2d');
            
            this.ctx = new Chart(_ctx, {
                type: 'bar',
                data: this.chartData,
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        }
    },
    watch: {
        chartData () {
            if(this.ctx) {
                this.ctx.destroy();
            }
            this.renderChart();
        }
    }
};
</script>