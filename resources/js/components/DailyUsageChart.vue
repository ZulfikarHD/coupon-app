<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
    type ChartOptions,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

interface DailyUsage {
    date: string;
    count: number;
}

interface Props {
    data: DailyUsage[];
}

const props = defineProps<Props>();

// Format date for display
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
    });
};

// Prepare chart data
const chartData = computed(() => {
    return {
        labels: props.data.map(item => formatDate(item.date)),
        datasets: [
            {
                label: 'Validasi Kupon',
                data: props.data.map(item => item.count),
                backgroundColor: props.data.map(item => {
                    const maxValue = Math.max(...props.data.map(d => d.count), 1);
                    const percentage = item.count / maxValue;
                    
                    if (percentage >= 0.8) {
                        return 'rgba(37, 99, 235, 0.8)'; // blue-600
                    } else if (percentage >= 0.5) {
                        return 'rgba(59, 130, 246, 0.8)'; // blue-500
                    } else {
                        return 'rgba(96, 165, 250, 0.8)'; // blue-400
                    }
                }),
                borderColor: props.data.map(item => {
                    const maxValue = Math.max(...props.data.map(d => d.count), 1);
                    const percentage = item.count / maxValue;
                    
                    if (percentage >= 0.8) {
                        return 'rgb(37, 99, 235)'; // blue-600
                    } else if (percentage >= 0.5) {
                        return 'rgb(59, 130, 246)'; // blue-500
                    } else {
                        return 'rgb(96, 165, 250)'; // blue-400
                    }
                }),
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            },
        ],
    };
});

// Chart options with iOS-style design
const chartOptions = computed<ChartOptions<'bar'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 14,
                weight: '600',
            },
            bodyFont: {
                size: 13,
            },
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
                title: (context) => {
                    const index = context[0].dataIndex;
                    const date = new Date(props.data[index].date);
                    return date.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    });
                },
                label: (context) => {
                    return `${context.parsed.y} validasi`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            border: {
                color: 'rgba(0, 0, 0, 0.1)',
                width: 1,
            },
            ticks: {
                color: 'rgba(0, 0, 0, 0.6)',
                font: {
                    size: 11,
                },
                maxRotation: props.data.length > 10 ? 45 : 0,
                minRotation: props.data.length > 10 ? 45 : 0,
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
                drawBorder: false,
            },
            border: {
                color: 'rgba(0, 0, 0, 0.1)',
                width: 1,
            },
            ticks: {
                color: 'rgba(0, 0, 0, 0.6)',
                font: {
                    size: 11,
                },
                stepSize: 1,
                precision: 0,
            },
        },
    },
}));
</script>

<template>
    <div class="w-full">
        <div class="h-[300px] w-full">
            <Bar
                :data="chartData"
                :options="chartOptions"
            />
        </div>
        
        <!-- Legend -->
        <div v-if="data.length > 0" class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-muted-foreground">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background: rgba(96, 165, 250, 0.8);"></div>
                <span>Rendah</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background: rgba(59, 130, 246, 0.8);"></div>
                <span>Sedang</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background: rgba(37, 99, 235, 0.8);"></div>
                <span>Tinggi</span>
            </div>
        </div>
    </div>
</template>
