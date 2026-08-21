<template>
  <div class="dashboard-page">
    <div class="page-header">
      <h2 class="title">Bảng Điều Khiển</h2>
      <p class="subtitle">Tổng quan tình hình hoạt động của phòng khám hôm nay</p>
    </div>

    <!-- Hàng 1: Các thẻ Thống kê (Stats Cards) -->
    <el-row :gutter="24" class="stat-cards-row">
      <!-- Thẻ Bệnh nhân -->
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Tổng Bệnh Nhân</span>
              <h3 class="stat-value">1,284</h3>
              <span class="stat-trend positive">
                <el-icon><Top /></el-icon> +12% so với tháng trước
              </span>
            </div>
            <div class="stat-icon-wrapper bg-blue">
              <el-icon><User /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Thẻ Lịch hẹn -->
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Lịch Hẹn Hôm Nay</span>
              <h3 class="stat-value">42</h3>
              <span class="stat-trend positive">
                <el-icon><Top /></el-icon> +5 ca mới
              </span>
            </div>
            <div class="stat-icon-wrapper bg-green">
              <el-icon><Calendar /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Thẻ Bác sĩ -->
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Bác Sĩ Trực</span>
              <h3 class="stat-value">8</h3>
              <span class="stat-trend neutral">
                <el-icon><Check /></el-icon> Đang hoạt động
              </span>
            </div>
            <div class="stat-icon-wrapper bg-purple">
              <el-icon><Avatar /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- Thẻ Doanh thu -->
      <el-col :xs="24" :sm="12" :lg="6">
        <el-card class="box-card stat-card" shadow="hover">
          <div class="stat-content">
            <div class="stat-info">
              <span class="stat-title">Doanh Thu (Ngày)</span>
              <h3 class="stat-value">24.5M</h3>
              <span class="stat-trend negative">
                <el-icon><Bottom /></el-icon> -2% so với hôm qua
              </span>
            </div>
            <div class="stat-icon-wrapper bg-orange">
              <el-icon><Money /></el-icon>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Hàng 2: Biểu đồ & Hoạt động gần đây -->
    <el-row :gutter="24" class="main-dashboard-row">
      <!-- Cột Trái: Biểu đồ (Placeholder) -->
      <el-col :xs="24" :lg="16">
        <el-card class="box-card chart-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">Thống kê Lượt khám (Tuần)</span>
              <el-button type="primary" link>Xem chi tiết</el-button>
            </div>
          </template>
          <div class="chart-placeholder">
            <!-- Khu vực này sau này bạn có thể cài thêm thư viện ECharts hoặc Chart.js để vẽ đồ thị -->
            <div class="mock-bars">
              <div class="bar" style="height: 60%;"></div>
              <div class="bar" style="height: 80%;"></div>
              <div class="bar" style="height: 40%;"></div>
              <div class="bar" style="height: 90%;"></div>
              <div class="bar" style="height: 50%;"></div>
              <div class="bar" style="height: 70%;"></div>
              <div class="bar" style="height: 100%;"></div>
            </div>
            <p class="chart-note">Vùng hiển thị Biểu đồ (Chart Area)</p>
          </div>
        </el-card>
      </el-col>

      <!-- Cột Phải: Lịch trình / Timeline -->
      <el-col :xs="24" :lg="8">
        <el-card class="box-card timeline-card" shadow="never">
          <template #header>
            <div class="card-header">
              <span class="card-title">Hoạt động mới nhất</span>
            </div>
          </template>
          <el-timeline>
            <el-timeline-item
              v-for="(activity, index) in recentActivities"
              :key="index"
              :type="activity.type"
              :color="activity.color"
              :timestamp="activity.time"
            >
              {{ activity.content }}
            </el-timeline-item>
          </el-timeline>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { User, Calendar, Avatar, Money, Top, Bottom, Check } from '@element-plus/icons-vue';

// Fake Data cho Timeline
const recentActivities = ref([
  {
    content: 'Bệnh nhân Nguyễn Văn A đã hoàn tất thanh toán',
    time: '10 phút trước',
    type: 'success',
    color: '#10b981'
  },
  {
    content: 'Lễ tân đã thêm mới 1 lịch hẹn lúc 14:00',
    time: '35 phút trước',
    type: 'primary',
    color: '#0ea5e9'
  },
  {
    content: 'Bác sĩ Lê C cập nhật hồ sơ bệnh án mã #BA092',
    time: '1 giờ trước',
    type: 'warning',
    color: '#f59e0b'
  },
  {
    content: 'Hủy lịch hẹn khám chuyên khoa Mắt',
    time: '2 giờ trước',
    type: 'danger',
    color: '#ef4444'
  }
]);
</script>

<style scoped>
.dashboard-page {
  animation: fadeIn 0.4s ease-in-out;
}

.stat-cards-row {
  margin-bottom: 24px;
}

.stat-card {
  border-radius: 20px !important;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.9) !important;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08) !important;
}

.stat-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-title {
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 28px;
  font-weight: 800;
  color: #0f172a;
  margin: 8px 0;
}

.stat-trend {
  font-size: 12px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}
.stat-trend.positive { color: #10b981; }
.stat-trend.negative { color: #ef4444; }
.stat-trend.neutral { color: #64748b; }

.stat-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}
.bg-blue { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
.bg-green { background: linear-gradient(135deg, #34d399, #059669); }
.bg-purple { background: linear-gradient(135deg, #a78bfa, #7c3aed); }
.bg-orange { background: linear-gradient(135deg, #fbbf24, #d97706); }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.chart-card, .timeline-card {
  height: 400px;
}

.chart-placeholder {
  height: 280px;
  background: #f1f5f9;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 1px dashed #cbd5e1;
}

.mock-bars {
  display: flex;
  align-items: flex-end;
  gap: 16px;
  height: 150px;
  width: 80%;
  margin-bottom: 16px;
}

.mock-bars .bar {
  flex: 1;
  background: linear-gradient(to top, #bae6fd, #38bdf8);
  border-radius: 6px 6px 0 0;
  transition: all 0.3s ease;
}
.mock-bars .bar:hover {
  background: linear-gradient(to top, #7dd3fc, #0284c7);
}

.chart-note {
  color: #94a3b8;
  font-size: 13px;
  font-weight: 500;
}

:deep(.el-timeline-item__content) {
  font-size: 13px;
  color: #334155;
  font-weight: 500;
}
:deep(.el-timeline-item__timestamp) {
  font-size: 12px;
  color: #94a3b8;
}
</style>