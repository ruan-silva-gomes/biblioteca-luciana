/**
 * Dashboard: Gráficos e Estatísticas
 */

async function initCharts(period = null) {
    const activePeriod = period || currentPeriod;
    const canvas = document.getElementById('flowChart');
    if (!canvas) return;

    if (flowChartInstance) flowChartInstance.destroy();

    try {
        const response = await fetch(`api.php?action=dashboard_stats&period=${activePeriod}`);
        const data = await response.json();

        const totalAcessos = document.getElementById('total-acessos');
        if (totalAcessos) {
            totalAcessos.innerText = data.total_acessos || 0;
        }

        const chartConfig = {
            type: currentChartType,
            data: {
                labels: ['08h', '09h', '10h', '11h', '12h', '13h', '14h', '15h', '16h', '17h'],
                datasets: [{
                    label: 'Fluxo de Pessoas',
                    data: data.flow_data || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    borderColor: '#D2232A',
                    backgroundColor: currentChartType === 'line' ? 'rgba(210, 35, 42, 0.1)' : '#D2232A',
                    fill: currentChartType === 'line',
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#D2232A',
                    pointBorderWidth: 2,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        };

        flowChartInstance = new Chart(canvas.getContext('2d'), chartConfig);

        const titles = { 'today': 'Hoje', 'week': 'Semana', 'month': 'Mês' };
        const periodTitle = document.getElementById('stat-period-title');
        if (periodTitle) periodTitle.innerText = `Acessos ${titles[activePeriod]}`;

    } catch (err) {
        console.error("Erro ao carregar dados do dashboard:", err);
    }
}

function changeChartType(type) {
    currentChartType = type;
    document.querySelectorAll('.chart-controls button').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById(`btn-type-${type}`);
    if (activeBtn) activeBtn.classList.add('active');
    initCharts();
}

function changePeriod(period) {
    currentPeriod = period;

    document.querySelectorAll('.period-selector button').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById(`btn-period-${period}`);
    if (activeBtn) activeBtn.classList.add('active');

    const excelBtn = document.getElementById('btn-export-dashboard');
    const pdfBtn = document.getElementById('btn-export-pdf');
    
    if (excelBtn || pdfBtn) {
        const labels = { 
            'today': 'de Hoje', 
            'week': 'da Semana', 
            'month': 'do Mês' 
        };
        
        if (excelBtn) {
            excelBtn.innerHTML = `<i class="fas fa-file-excel"></i> Planilha ${labels[period]}`;
            excelBtn.setAttribute('onclick', `exportExcel('${period}')`);
        }
        
        if (pdfBtn) {
            pdfBtn.innerHTML = `<i class="fas fa-file-pdf"></i> PDF ${labels[period]} (Com Gráficos)`;
            pdfBtn.setAttribute('onclick', `exportDashboardPDF('${period}')`);
        }
    }

    initCharts(period);
}
