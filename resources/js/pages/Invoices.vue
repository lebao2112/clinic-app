<template>
  <div class="invoices-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Hóa đơn</h2>
        <span class="subtitle">Theo dõi, cập nhật trạng thái thanh toán và quản lý doanh thu phòng khám</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Tạo Hóa đơn mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm theo mã hóa đơn..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="invoices" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column label="Mã Hóa Đơn" min-width="160" align="center">
          <template #default="scope">
            <el-tag type="info">{{ scope.row.invoice_code || 'N/A' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Tạm tính (Subtotal)" width="160" align="right">
          <template #default="scope"><span style="font-family: monospace;">{{ formatCurrency(scope.row.subtotal) }}</span></template>
        </el-table-column>
        <el-table-column label="Giảm giá" width="130" align="right">
          <template #default="scope"><span style="color: #F56C6C; font-family: monospace;">- {{ formatCurrency(scope.row.discount || 0) }}</span></template>
        </el-table-column>
        <el-table-column label="Tổng thực thu" width="160" align="right">
          <template #default="scope">
            <strong style="color: #10b981; font-family: monospace;">
              {{ formatCurrency(scope.row.total) }}
            </strong>
          </template>
        </el-table-column>
        <el-table-column label="Trạng thái" width="150" align="center">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)" class="status-tag">{{ getStatusLabel(scope.row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Xem chi tiết" placement="top">
                <el-button type="info" link @click="handleView(scope.row)"><el-icon :size="18"><View /></el-icon></el-button>
              </el-tooltip>
              <el-tooltip content="Hủy hóa đơn" placement="top">
                <el-button type="danger" link @click="handleCancel(scope.row)" :disabled="scope.row.status !== 'unpaid'">
                  <el-icon :size="18"><CloseBold /></el-icon>
                </el-button>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Chưa có dữ liệu hóa đơn nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalInvoices" :page-size="10" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG TẠO HÓA ĐƠN MỚI -->
    <el-dialog v-model="dialogVisible" title="Tạo Hóa Đơn Mới (Tự động tính tiền)" width="450px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="ID Phiếu khám (Examination ID)" prop="examination_id">
          <el-input v-model.number="form.examination_id" placeholder="Nhập ID phiếu khám..." size="large" />
        </el-form-item>

        <el-form-item label="Tiền giảm giá (Discount - VNĐ)" prop="discount">
          <el-input v-model.number="form.discount" placeholder="0" size="large" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            Tạo hóa đơn
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, View, CloseBold } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const invoices = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const totalInvoices = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({
  examination_id: '',
  discount: 0
});

const rules = {
  examination_id: [{ required: true, message: 'Vui lòng nhập ID phiếu khám', trigger: 'blur' }]
};

const formatCurrency = (value) => {
  if (!value) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const getStatusType = (status) => {
  const map = { 'unpaid': 'warning', 'paid': 'success', 'cancelled': 'danger' };
  return map[status] || 'info';
};

const getStatusLabel = (status) => {
  const map = { 'unpaid': 'CHƯA THANH TOÁN', 'paid': 'ĐÃ THANH TOÁN', 'cancelled': 'ĐÃ HỦY' };
  return map[status] || status || 'N/A';
};

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const response = await axios.get(`/api/invoices?page=${page}&search=${searchQuery.value}`);
    invoices.value = response.data.data || response.data;
    totalInvoices.value = response.data.meta?.total || response.data.total || invoices.value.length;
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const handleSearch = () => fetchInvoices(1);
const handlePageChange = (page) => fetchInvoices(page);

const openCreateDialog = () => {
  form.examination_id = '';
  form.discount = 0;
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        await axios.post('/api/invoices', form);
        ElMessage.success('Tạo hóa đơn thành công!');
        dialogVisible.value = false;
        fetchInvoices(currentPage.value);
      } catch (error) {
        const errorMsg = error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại!';
        ElMessage.error(errorMsg);
      } finally {
        submitting.value = false;
      }
    }
  });
};

const handleView = (row) => {
  ElMessage.info(`Hóa đơn ${row.invoice_code} - Tạm tính: ${formatCurrency(row.subtotal)} | Giảm: ${formatCurrency(row.discount)} | Thực thu: ${formatCurrency(row.total)}`);
};

const handleCancel = (row) => {
  ElMessageBox.confirm(
    `Bạn có chắc chắn muốn hủy hóa đơn ${row.invoice_code} không?`,
    'Xác nhận hủy',
    { confirmButtonText: 'Hủy hóa đơn', cancelButtonText: 'Quay lại', type: 'warning' }
  ).then(async () => {
    try {
      await axios.patch(`/api/invoices/${row.id}/cancel`);
      ElMessage.success('Đã hủy hóa đơn thành công!');
      fetchInvoices(currentPage.value);
    } catch (error) {
      const errorMsg = error.response?.data?.message || 'Không thể hủy hóa đơn này!';
      ElMessage.error(errorMsg);
    }
  }).catch(() => {});
};

onMounted(() => { fetchInvoices(); });
</script>