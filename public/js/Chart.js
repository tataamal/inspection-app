// Data dummy untuk MRP
    const mrpData = [
      { code: 'D23', count: 89, percentage: 23.5, lastStatus: 'PASS', trend: 'up' },
      { code: 'A15', count: 76, percentage: 20.1, lastStatus: 'PASS', trend: 'up' },
      { code: 'C08', count: 67, percentage: 17.7, lastStatus: 'REJECT', trend: 'down' },
      { code: 'B42', count: 54, percentage: 14.3, lastStatus: 'PASS', trend: 'stable' },
      { code: 'E19', count: 48, percentage: 12.7, lastStatus: 'PASS', trend: 'up' },
      { code: 'F33', count: 32, percentage: 8.5, lastStatus: 'REJECT', trend: 'down' },
      { code: 'G07', count: 13, percentage: 3.4, lastStatus: 'PASS', trend: 'stable' }
    ];

    // Chart Defect Found
    const defectCtx = document.getElementById('defectChart').getContext('2d');
    const defectChart = new Chart(defectCtx, {
      type: 'bar',
      data: {
        labels: ['Lem', 'Warna', 'Retak', 'Cacat Kayu', 'Ukuran'],
        datasets: [{
          label: 'Jumlah Ditemukan',
          data: [12, 19, 3, 5, 9],
          backgroundColor: 'rgba(34,197,94,0.7)',
          borderColor: 'rgba(34,197,94,1)',
          borderWidth: 1,
          borderRadius: 6,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0,0,0,0.1)'
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // Chart MRP Inspeksi
    const mrpCtx = document.getElementById('mrpChart').getContext('2d');
    const mrpChart = new Chart(mrpCtx, {
      type: 'doughnut',
      data: {
        labels: mrpData.map(item => item.code),
        datasets: [{
          data: mrpData.map(item => item.count),
          backgroundColor: [
            '#10B981', // emerald-500
            '#3B82F6', // blue-500
            '#F59E0B', // amber-500
            '#EF4444', // red-500
            '#8B5CF6', // violet-500
            '#06B6D4', // cyan-500
            '#84CC16'  // lime-500
          ],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true,
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const data = mrpData[context.dataIndex];
                return `${data.code}: ${data.count} inspeksi (${data.percentage}%)`;
              }
            }
          }
        }
      }
    });

    // Populate MRP Table
    function populateMrpTable() {
      const tbody = document.getElementById('mrpTableBody');
      let html = '';
      
      mrpData.forEach((item, index) => {
        const trendIcon = item.trend === 'up' ? '📈' : item.trend === 'down' ? '📉' : '➡️';
        const statusClass = item.lastStatus === 'PASS' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        const trendClass = item.trend === 'up' ? 'text-green-600' : item.trend === 'down' ? 'text-red-600' : 'text-gray-600';
        
        html += `
          <tr class="hover:bg-gray-50">
            <td class="p-3 border font-semibold">#${index + 1}</td>
            <td class="p-3 border font-mono font-bold text-blue-600">${item.code}</td>
            <td class="p-3 border text-center font-semibold">${item.count}</td>
            <td class="p-3 border text-center">${item.percentage}%</td>
            <td class="p-3 border text-center">
              <span class="px-2 py-1 rounded-full text-xs font-semibold ${statusClass}">${item.lastStatus}</span>
            </td>
            <td class="p-3 border text-center ${trendClass}">
              ${trendIcon} ${item.trend.toUpperCase()}
            </td>
          </tr>
        `;
      });
      
      tbody.innerHTML = html;
    }

    // Animate counters
    function animateCounters() {
      const counters = [
        { id: 'totalInspections', target: 1247 },
        { id: 'avgPerDay', target: 42 },
        { id: 'efficiency', target: 87, suffix: '%' }
      ];

      counters.forEach(counter => {
        const element = document.getElementById(counter.id);
        let current = 0;
        const increment = counter.target / 30;
        
        const timer = setInterval(() => {
          current += increment;
          if (current >= counter.target) {
            current = counter.target;
            clearInterval(timer);
          }
          element.textContent = Math.floor(current) + (counter.suffix || '');
        }, 50);
      });
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
      populateMrpTable();
      setTimeout(animateCounters, 500);
    });