<script setup lang="ts">
import { computed, ref } from 'vue';

interface DailyUsage {
    date: string;
    count: number;
}

interface Props {
    data: DailyUsage[];
}

const props = defineProps<Props>();

// Chart dimensions
const chartHeight = 280;
const chartPadding = { top: 20, right: 20, bottom: 50, left: 50 };
const chartWidth = computed(() => {
    const minWidth = 600;
    const calculatedWidth = props.data.length * Math.max(30, Math.min(50, 600 / props.data.length));
    return Math.max(minWidth, calculatedWidth);
});

// Calculate max value for scaling
const maxValue = computed(() => {
    if (props.data.length === 0) return 1;
    return Math.max(...props.data.map(d => d.count), 1);
});

// Calculate bar width and spacing
const barWidth = computed(() => {
    const availableWidth = chartWidth.value - chartPadding.left - chartPadding.right;
    const spacing = 8;
    const totalSpacing = spacing * (props.data.length - 1);
    return Math.max(8, (availableWidth - totalSpacing) / props.data.length);
});

// Format date for display
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
    });
};

// Format date for full display
const formatDateFull = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

// Calculate bar height
const getBarHeight = (value: number): number => {
    if (maxValue.value === 0) return 0;
    const availableHeight = chartHeight - chartPadding.top - chartPadding.bottom;
    return (value / maxValue.value) * availableHeight;
};

// Calculate bar X position
const getBarX = (index: number): number => {
    return chartPadding.left + index * (barWidth.value + 8);
};

// Generate Y-axis labels
const yAxisLabels = computed(() => {
    const steps = 5;
    const labels: number[] = [];
    const step = Math.ceil(maxValue.value / steps);
    
    for (let i = 0; i <= steps; i++) {
        labels.push(i * step);
    }
    
    return labels;
});

// Get bar color based on value (iOS-style gradient)
const getBarColor = (value: number): string => {
    const percentage = maxValue.value > 0 ? value / maxValue.value : 0;
    
    if (percentage >= 0.8) {
        return 'url(#gradient-high)';
    } else if (percentage >= 0.5) {
        return 'url(#gradient-medium)';
    } else {
        return 'url(#gradient-low)';
    }
};

// Hover state
const hoveredIndex = ref<number | null>(null);
</script>

<template>
    <div class="w-full overflow-x-auto">
        <div :style="{ width: `${chartWidth}px`, minWidth: '100%' }" class="relative">
            <svg
                :width="chartWidth"
                :height="chartHeight + 60"
                class="w-full h-auto"
                :viewBox="`0 0 ${chartWidth} ${chartHeight + 60}`"
                preserveAspectRatio="xMidYMid meet"
            >
                <!-- Gradient definitions -->
                <defs>
                    <linearGradient id="gradient-high" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#2563eb;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="gradient-medium" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#60a5fa;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:1" />
                    </linearGradient>
                    <linearGradient id="gradient-low" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#93c5fd;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#60a5fa;stop-opacity:1" />
                    </linearGradient>
                </defs>

                <!-- Grid lines -->
                <g v-for="(label, index) in yAxisLabels" :key="`grid-${index}`">
                    <line
                        :x1="chartPadding.left"
                        :y1="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) * (1 - label / maxValue)"
                        :x2="chartWidth - chartPadding.right"
                        :y2="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) * (1 - label / maxValue)"
                        stroke="currentColor"
                        stroke-width="1"
                        stroke-opacity="0.1"
                        class="text-border"
                    />
                </g>

                <!-- Y-axis labels -->
                <g v-for="(label, index) in yAxisLabels" :key="`y-label-${index}`">
                    <text
                        :x="chartPadding.left - 10"
                        :y="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) * (1 - label / maxValue) + 4"
                        text-anchor="end"
                        class="text-xs fill-muted-foreground"
                        font-size="12"
                    >
                        {{ label }}
                    </text>
                </g>

                <!-- Bars -->
                <g v-for="(item, index) in data" :key="`bar-${index}`">
                    <rect
                        :x="getBarX(index)"
                        :y="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) - getBarHeight(item.count)"
                        :width="barWidth"
                        :height="getBarHeight(item.count)"
                        :fill="getBarColor(item.count)"
                        :opacity="hoveredIndex === index ? 1 : hoveredIndex === null ? 1 : 0.3"
                        rx="4"
                        ry="4"
                        class="transition-all duration-200 cursor-pointer"
                        @mouseenter="hoveredIndex = index"
                        @mouseleave="hoveredIndex = null"
                    />
                    
                    <!-- Value label on hover -->
                    <g v-if="hoveredIndex === index">
                        <rect
                            :x="getBarX(index) - 20"
                            :y="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) - getBarHeight(item.count) - 30"
                            width="40"
                            height="24"
                            rx="6"
                            fill="currentColor"
                            class="text-foreground"
                            opacity="0.95"
                        />
                        <text
                            :x="getBarX(index)"
                            :y="chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) - getBarHeight(item.count) - 12"
                            text-anchor="middle"
                            class="text-xs fill-background font-semibold"
                            font-size="11"
                        >
                            {{ item.count }}
                        </text>
                    </g>
                </g>

                <!-- X-axis labels -->
                <g v-for="(item, index) in data" :key="`x-label-${index}`">
                    <text
                        :x="getBarX(index) + barWidth / 2"
                        :y="chartHeight - chartPadding.bottom + 15"
                        text-anchor="middle"
                        class="text-xs fill-muted-foreground"
                        font-size="10"
                        :transform="data.length > 10 ? `rotate(-45 ${getBarX(index) + barWidth / 2} ${chartHeight - chartPadding.bottom + 15})` : ''"
                    >
                        {{ formatDate(item.date) }}
                    </text>
                </g>

                <!-- Axis lines -->
                <line
                    :x1="chartPadding.left"
                    :y1="chartPadding.top"
                    :x2="chartPadding.left"
                    :y2="chartHeight - chartPadding.bottom"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-opacity="0.2"
                    class="text-border"
                />
                <line
                    :x1="chartPadding.left"
                    :y1="chartHeight - chartPadding.bottom"
                    :x2="chartWidth - chartPadding.right"
                    :y2="chartHeight - chartPadding.bottom"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-opacity="0.2"
                    class="text-border"
                />
            </svg>

            <!-- Tooltip -->
            <div
                v-if="hoveredIndex !== null && data[hoveredIndex]"
                class="absolute bg-background/95 backdrop-blur-xl border border-border rounded-xl shadow-lg p-3 pointer-events-none z-10"
                :style="{
                    left: `${getBarX(hoveredIndex) + barWidth / 2}px`,
                    top: `${chartPadding.top + (chartHeight - chartPadding.top - chartPadding.bottom) - getBarHeight(data[hoveredIndex].count) - 60}px`,
                    transform: 'translateX(-50%)',
                }"
            >
                <div class="text-sm font-semibold text-foreground">
                    {{ formatDateFull(data[hoveredIndex].date) }}
                </div>
                <div class="text-xs text-muted-foreground mt-1">
                    {{ data[hoveredIndex].count }} validasi
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div v-if="data.length > 0" class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-muted-foreground">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background: linear-gradient(to bottom, #93c5fd, #60a5fa);"></div>
                <span>Rendah</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background: linear-gradient(to bottom, #60a5fa, #3b82f6);"></div>
                <span>Sedang</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded" style="background: linear-gradient(to bottom, #3b82f6, #2563eb);"></div>
                <span>Tinggi</span>
            </div>
        </div>
    </div>
</template>
