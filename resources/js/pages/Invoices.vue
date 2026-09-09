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
        <el-table-column type="expand">
          <template #default="props">
            <div class="expanded-detail" style="padding: 15px 30px; background-color: #f8fafc; border-radius: 8px;">
              <h4 style="color: #0284c7; margin-top: 0; margin-bottom: 12px;">Chi tiết Hóa đơn: {{ props.row.invoice_code }}</h4>
              
              <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; background: #ffffff; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                <div>
                  <span style="font-size: 12px; color: #64748b;">Phí khám bệnh:</span>
                  <div style="font-weight: bold; font-family: monospace;">{{ formatCurrency(props.row.breakdown?.examination_fee || 0) }}</div>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748b;">Tổng tiền thuốc:</span>
                  <div style="font-weight: bold; font-family: monospace;">{{ formatCurrency(props.row.breakdown?.medicine_total || 0) }}</div>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748b;">Giảm giá:</span>
                  <div style="font-weight: bold; font-family: monospace; color: #e11d48;">- {{ formatCurrency(props.row.discount || 0) }}</div>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748b;">Tổng thực thu:</span>
                  <div style="font-weight: bold; font-family: monospace; color: #16a34a;">{{ formatCurrency(props.row.total || 0) }}</div>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748b;">Đã thanh toán:</span>
                  <div style="font-weight: bold; font-family: monospace; color: #0284c7;">{{ formatCurrency(props.row.paid_amount || 0) }}</div>
                </div>
                <div>
                  <span style="font-size: 12px; color: #64748b;">Còn lại phải thu:</span>
                  <div style="font-weight: bold; font-family: monospace; color: #e11d48;">{{ formatCurrency(props.row.remaining_amount ?? (props.row.total - (props.row.paid_amount || 0))) }}</div>
                </div>
              </div>

              <h5 style="margin: 10px 0 8px 0; color: #334155;">Danh sách thuốc trong đơn:</h5>
              <el-table :data="props.row.breakdown?.items || []" border size="small">
                <el-table-column prop="medicine_name" label="Tên thuốc" min-width="150" />
                <el-table-column prop="quantity" label="Số lượng" width="90" align="center" />
                <el-table-column label="Đơn giá" width="120" align="right">
                  <template #default="scope">{{ formatCurrency(scope.row.unit_price) }}</template>
                </el-table-column>
                <el-table-column label="Thành tiền" width="130" align="right">
                  <template #default="scope"><strong>{{ formatCurrency(scope.row.total_price) }}</strong></template>
                </el-table-column>
                <el-table-column prop="dosage" label="Liều lượng" min-width="110" />
                <el-table-column prop="usage_instruction" label="Cách dùng" min-width="130" />
              </el-table>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column label="Mã Hóa Đơn" min-width="150" align="center">
          <template #default="scope">
            <el-tag type="info">{{ scope.row.invoice_code || 'N/A' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Tổng thực thu" width="140" align="right">
          <template #default="scope">
            <strong style="color: #10b981; font-family: monospace;">
              {{ formatCurrency(scope.row.total) }}
            </strong>
          </template>
        </el-table-column>
        <el-table-column label="Đã trả" width="130" align="right">
          <template #default="scope">
            <span style="color: #0284c7; font-family: monospace;">
              {{ formatCurrency(scope.row.paid_amount || 0) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="Còn lại" width="130" align="right">
          <template #default="scope">
            <span style="color: #e11d48; font-family: monospace; font-weight: bold;">
              {{ formatCurrency(scope.row.remaining_amount ?? (scope.row.total - (scope.row.paid_amount || 0))) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="Trạng thái" width="130" align="center">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)" class="status-tag">{{ getStatusLabel(scope.row.status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Thao tác" width="200" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Xem chi tiết hóa đơn" placement="top">
                <el-button type="primary" link @click="handleView(scope.row)">
                  <el-icon :size="18"><View /></el-icon>
                </el-button>
              </el-tooltip>
              
              <el-tooltip content="Chỉnh sửa giảm giá" placement="top">
                <el-button type="warning" link @click="openEditDialog(scope.row)" :disabled="scope.row.status !== 'unpaid'">
                  <el-icon :size="18"><Edit /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Thanh toán qua PayPal" placement="top">
                <el-button type="success" link @click="handlePayment(scope.row)" :disabled="scope.row.status !== 'unpaid'">
                  <el-icon :size="18"><Money /></el-icon>
                </el-button>
              </el-tooltip>
              
              <el-tooltip content="Hủy hóa đơn" placement="top">
                <el-button type="danger" link @click="handleCancel(scope.row)" :disabled="scope.row.status !== 'unpaid'">
                  <el-icon :size="18"><Delete /></el-icon>
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

    <el-dialog v-model="dialogVisible" title="Tạo Hóa Đơn Mới (Tự động tính tiền)" width="450px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="Chọn Phiếu khám (Examination)" prop="examination_id">
          <el-select 
            v-model="form.examination_id" 
            placeholder="Click chọn hoặc gõ tìm kiếm ID..." 
            size="large" 
            style="width: 100%"
            filterable
            :loading="loadingExaminations"
          >
            <el-option
              v-for="exam in availableExaminations"
              :key="exam.id"
              :label="`Phiếu khám ID: ${exam.id} ${exam.patient?.full_name ? '- Bệnh nhân: ' + exam.patient.full_name : ''}`"
              :value="exam.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="Tiền giảm giá (Discount - VNĐ)" prop="discount">
          <el-input v-model.number="form.discount" placeholder="0" size="large" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" class="btn-cancel">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" class="btn-add">
            Tạo hóa đơn
          </el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog v-model="editDialogVisible" title="Cập nhật Giảm giá Hóa đơn" width="400px" destroy-on-close>
      <el-form :model="editForm" label-position="top">
        <el-form-item label="Tiền giảm giá (Discount - VNĐ)">
          <el-input v-model.number="editForm.discount" placeholder="Nhập số tiền..." size="large" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="editDialogVisible = false" class="btn-cancel">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submittingEdit" @click="submitEdit" class="btn-add">
            Lưu thay đổi
          </el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog v-model="paymentDialogVisible" title="Thanh toán hóa đơn" width="400px" destroy-on-close>
      <el-form :model="paymentForm" label-position="top">
        <el-form-item label="Tổng thực thu của hóa đơn:">
          <div style="font-weight: bold; color: #16a34a; font-size: 15px;">
            {{ formatCurrency(payingInvoice?.total || 0) }}
          </div>
        </el-form-item>
        
        <el-form-item label="Số tiền muốn thanh toán (VNĐ)">
          <el-input v-model.number="paymentForm.amount" placeholder="Nhập số tiền..." size="large" />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="paymentDialogVisible = false" class="btn-cancel">Hủy bỏ</el-button>
          <el-button type="primary" @click="submitPayment" class="btn-add">
            Tiếp tục thanh toán
          </el-button>
        </span>
      </template>
    </el-dialog>

    <el-dialog v-model="viewDialogVisible" :title="`Chi tiết Hóa đơn: ${viewingInvoice?.invoice_code || ''}`" width="750px" destroy-on-close>
      <div v-if="viewingInvoice" class="invoice-detail-container">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
          <div><strong>Mã hóa đơn:</strong> {{ viewingInvoice.invoice_code }}</div>
          <div><strong>Trạng thái:</strong> <el-tag :type="getStatusType(viewingInvoice.status)">{{ getStatusLabel(viewingInvoice.status) }}</el-tag></div>
          <div><strong>Phí khám bệnh:</strong> {{ formatCurrency(viewingInvoice.breakdown?.examination_fee || 0) }}</div>
          <div><strong>Tổng tiền thuốc:</strong> {{ formatCurrency(viewingInvoice.breakdown?.medicine_total || 0) }}</div>
          <div><strong>Giảm giá:</strong> <span style="color: #e11d48;">- {{ formatCurrency(viewingInvoice.discount || 0) }}</span></div>
          <div><strong>Tổng thực thu:</strong> <strong style="color: #16a34a;">{{ formatCurrency(viewingInvoice.total || 0) }}</strong></div>
          <div><strong>Đã thanh toán:</strong> <span style="color: #0284c7;">{{ formatCurrency(viewingInvoice.paid_amount || 0) }}</span></div>
          <div><strong>Còn lại phải thu:</strong> <strong style="color: #e11d48;">{{ formatCurrency(viewingInvoice.remaining_amount ?? (viewingInvoice.total - (viewingInvoice.paid_amount || 0))) }}</strong></div>
        </div>

        <h4 style="margin-bottom: 10px; color: #0284c7;">Danh sách thuốc kê đơn:</h4>
        <el-table :data="viewingInvoice.breakdown?.items || []" border size="small">
          <el-table-column prop="medicine_name" label="Tên thuốc" min-width="140" />
          <el-table-column prop="quantity" label="SL" width="60" align="center" />
          <el-table-column label="Đơn giá" width="110" align="right">
            <template #default="scope">{{ formatCurrency(scope.row.unit_price) }}</template>
          </el-table-column>
          <el-table-column label="Thành tiền" width="120" align="right">
            <template #default="scope"><strong>{{ formatCurrency(scope.row.total_price) }}</strong></template>
          </el-table-column>
          <el-table-column prop="dosage" label="Liều lượng" min-width="100" />
          <el-table-column prop="usage_instruction" label="Cách dùng" min-width="120" />
        </el-table>
      </div>
      <template #footer>
        <el-button type="primary" @click="viewDialogVisible = false" size="large">Đóng</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, View, Delete, Money, Edit } from '@element-plus/icons-vue';
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

const viewDialogVisible = ref(false);
const viewingInvoice = ref(null);

const editDialogVisible = ref(false);
const submittingEdit = ref(false);
const editingInvoice = ref(null);
const editForm = reactive({
  discount: 0
});

const paymentDialogVisible = ref(false);
const payingInvoice = ref(null);
const paymentForm = reactive({
  amount: 0
});

const availableExaminations = ref([]);
const loadingExaminations = ref(false);

const form = reactive({
  examination_id: '',
  discount: 0
});

const rules = {
  examination_id: [{ required: true, message: 'Vui lòng chọn Phiếu khám', trigger: 'change' }]
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
  const map = { 'unpaid': 'Chưa thanh toán', 'paid': 'Đã thanh toán', 'cancelled': 'Đã hủy' };
  return map[status] || status || 'N/A';
};

const fetchInvoices = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const response = await axios.get(`/api/invoices?page=${page}&search=${searchQuery.value}`);
    const resData = response.data;
    
    let items = [];
    let total = 0;

    if (Array.isArray(resData)) {
      items = resData;
      total = items.length;
    } else if (resData.data && Array.isArray(resData.data)) {
      items = resData.data;
      total = resData.total || resData.meta?.total || items.length;
    } else if (resData.data && resData.data.data && Array.isArray(resData.data.data)) {
      items = resData.data.data;
      total = resData.data.total || resData.meta?.total || items.length;
    }

    invoices.value = items;
    totalInvoices.value = total;

  } catch (error) {
    console.error('Lỗi khi tải hóa đơn:', error);
    invoices.value = [];
    totalInvoices.value = 0;
  } finally {
    loading.value = false;
  }
};

const fetchExaminations = async () => {
  loadingExaminations.value = true;
  try {
    const response = await axios.get('/api/examinations');
    const resData = response.data;
    
    let fetchedExams = [];
    if (Array.isArray(resData)) {
      fetchedExams = resData;
    } else if (resData.data && Array.isArray(resData.data)) {
      fetchedExams = resData.data;
    } else if (resData.data && resData.data.data && Array.isArray(resData.data.data)) {
      fetchedExams = resData.data.data;
    }

    availableExaminations.value = fetchedExams.filter(exam => {
      if (exam.invoice || exam.invoice_id) return false;
      if (exam.payment_status && exam.payment_status !== 'unpaid') return false;

      const isAlreadyInvoiced = invoices.value.some(inv => 
        inv.examination_id === exam.id || 
        (inv.breakdown && inv.breakdown.examination_id === exam.id)
      );
      
      return !isAlreadyInvoiced;
    });

  } catch (error) {
    ElMessage.error('Không thể tải danh sách phiếu khám!');
    console.error(error);
  } finally {
    loadingExaminations.value = false;
  }
};

const handleSearch = () => fetchInvoices(1);
const handlePageChange = (page) => fetchInvoices(page);

const openCreateDialog = () => {
  form.examination_id = '';
  form.discount = 0;
  dialogVisible.value = true;
  fetchExaminations();
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

const openEditDialog = (row) => {
  editingInvoice.value = row;
  editForm.discount = row.discount || 0;
  editDialogVisible.value = true;
};

const submitEdit = async () => {
  if (!editingInvoice.value) return;
  if (editForm.discount < 0) {
    ElMessage.error('Số tiền giảm giá không được là số âm!');
    return;
  }
  if (editForm.discount > editingInvoice.value.subtotal) {
    ElMessage.error(`Giảm giá không được vượt quá số tiền tạm tính (${formatCurrency(editingInvoice.value.subtotal)})!`);
    return;
  }
  submittingEdit.value = true;
  try {
    await axios.patch(`/api/invoices/${editingInvoice.value.id}/discount`, {
      discount: editForm.discount
    });
    ElMessage.success('Cập nhật giảm giá thành công!');
    editDialogVisible.value = false;
    fetchInvoices(currentPage.value);
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể cập nhật hóa đơn!';
    ElMessage.error(errorMsg);
  } finally {
    submittingEdit.value = false;
  }
};

const handleView = (row) => {
  viewingInvoice.value = row;
  viewDialogVisible.value = true;
};

const handlePayment = (row) => {
  payingInvoice.value = row;
  const remaining = row.remaining_amount ?? (row.total - (row.paid_amount || 0));
  paymentForm.amount = remaining > 0 ? remaining : row.total;
  paymentDialogVisible.value = true;
};

const submitPayment = async () => {
  if (!payingInvoice.value) return;

  if (paymentForm.amount <= 0) {
    ElMessage.error('Số tiền thanh toán phải lớn hơn 0!');
    return;
  }

  const remaining = payingInvoice.value.remaining_amount ?? (payingInvoice.value.total - (payingInvoice.value.paid_amount || 0));
  if (paymentForm.amount > remaining) {
    ElMessage.error(`Số tiền thanh toán không được vượt quá số tiền còn lại (${formatCurrency(remaining)})!`);
    return;
  }

  try {
    ElMessage.info('Đang kết nối tới cổng thanh toán PayPal...');
    
    const response = await axios.post(`/api/invoices/${payingInvoice.value.id}/payments`, {
      method: 'paypal',
      amount: paymentForm.amount
    });
    
    const approvalUrl = response.data.approval_url || response.data.data?.approval_url;
    
    if (approvalUrl) {
      window.location.href = approvalUrl;
    } else {
      ElMessage.error('Không nhận được đường dẫn thanh toán từ hệ thống!');
    }
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Không thể khởi tạo giao dịch thanh toán!';
    ElMessage.error(errorMsg);
  } finally {
    paymentDialogVisible.value = false;
  }
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

onMounted(() => { 
  fetchInvoices(); 
  const urlParams = new URLSearchParams(window.location.search);
  const paymentStatus = urlParams.get('payment');
  
  if (paymentStatus === 'cancelled') {
    ElMessage.warning('Đã hủy thanh toán PayPal!');
  } else if (paymentStatus === 'success') {
    ElMessage.success('Thanh toán PayPal thành công!');
  } else if (paymentStatus === 'failed') {
    ElMessage.error('Thanh toán PayPal thất bại!');
  }
  if (paymentStatus) {
    window.history.replaceState(null, '', window.location.pathname);
  }
});
</script>

<style scoped>
.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.dialog-footer .el-button {
  height: 40px;
  padding: 0 20px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
}

.btn-cancel {
  border: 1px solid #dcdfe6 !important;
  color: #606266 !important;
  background-color: #ffffff !important;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  color: #409eff !important;
  border-color: #c6e2ff !important;
  background-color: #ecf5ff !important;
}
</style>