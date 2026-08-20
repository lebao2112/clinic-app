<template>
  <div class="prescriptions-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Đơn thuốc</h2>
        <span class="subtitle">Kê đơn và theo dõi chi tiết các loại thuốc cho bệnh nhân</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Kê Đơn thuốc mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm đơn thuốc..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="prescriptions" class="custom-table" style="width: 100%">
        <!-- Nút Expand để xem chi tiết các thuốc trong đơn -->
        <el-table-column type="expand">
          <template #default="props">
            <div class="expanded-detail">
              <h4>Chi tiết thuốc trong đơn:</h4>
              <el-table :data="props.row.items || props.row.prescription_items" border size="small" style="margin-top: 10px;">
                <el-table-column label="Tên thuốc" min-width="150">
                  <template #default="scope">
                    {{ getMedicineName(scope.row.medicine_id) }}
                  </template>
                </el-table-column>
                <el-table-column prop="quantity" label="Số lượng" width="100" align="center" />
                <el-table-column prop="dosage" label="Liều lượng" min-width="120" />
                <el-table-column prop="usage_instruction" label="Cách dùng" min-width="150" />
              </el-table>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column label="Mã Phiếu Khám" width="150" align="center">
          <template #default="scope">
            <strong>#EXM-{{ scope.row.examination_id }}</strong>
          </template>
        </el-table-column>
        <el-table-column label="Bác sĩ kê đơn" min-width="160">
          <template #default="scope">
            <span class="user-name">{{ getDoctorName(scope.row.doctor_id) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="notes" label="Ghi chú" min-width="200" show-overflow-tooltip />
        <el-table-column prop="created_at" label="Ngày kê đơn" width="180" align="center" />
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <!-- Nút Sửa Đã Được Mở Lại -->
              <el-tooltip content="Chỉnh sửa đơn thuốc" placement="top">
                <el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button>
              </el-tooltip>
              <el-tooltip content="Xóa đơn thuốc" placement="top">
                <el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Chưa có đơn thuốc nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalPrescriptions" :page-size="15" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG KÊ ĐƠN THUỐC (MASTER-DETAIL) -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh Sửa Đơn Thuốc' : 'Kê Đơn Thuốc Mới'" width="800px" destroy-on-close top="5vh">
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        
        <el-row :gutter="20">
          <el-col :span="24">
            <el-form-item label="Phiếu khám (Examination)" prop="examination_id">
              <el-select v-model="form.examination_id" placeholder="Chọn phiếu khám..." size="large" style="width: 100%;" filterable :disabled="isEditMode">
                <el-option
                  v-for="exm in examinationsList"
                  :key="exm.id"
                  :label="`Phiếu khám #EXM-${exm.id} - Chẩn đoán: ${exm.diagnosis || 'N/A'}`"
                  :value="exm.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="Ghi chú đơn thuốc (Notes)" prop="notes">
              <el-input v-model="form.notes" placeholder="VD: Bệnh nhân nhớ uống nhiều nước..." type="textarea" rows="2" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-divider>Chi Tiết Thuốc Kê Đơn</el-divider>

        <div style="margin-bottom: 15px; display: flex; justify-content: flex-end;">
          <el-button type="success" size="small" @click="addPrescriptionItem">
            <el-icon><Plus /></el-icon> Thêm loại thuốc
          </el-button>
        </div>

        <div v-for="(item, index) in form.items" :key="index" class="prescription-item-row">
          <div class="item-header">
            <span>Thuốc #{{ index + 1 }}</span>
            <el-button type="danger" link @click="removePrescriptionItem(index)" v-if="form.items.length > 1">
              <el-icon><Close /></el-icon> Xóa
            </el-button>
          </div>
          <el-row :gutter="10">
            <el-col :span="8">
              <el-form-item :prop="`items.${index}.medicine_id`" :rules="{ required: true, message: 'Chọn thuốc', trigger: 'change' }">
                <el-select v-model="item.medicine_id" placeholder="Chọn thuốc..." filterable style="width: 100%;">
                  <el-option
                    v-for="med in medicinesList"
                    :key="med.id"
                    :label="`${med.code} - ${med.name} (Kho: ${med.stock})`"
                    :value="med.id"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="4">
              <el-form-item :prop="`items.${index}.quantity`" :rules="{ required: true, message: 'SL', trigger: 'blur' }">
                <el-input-number v-model="item.quantity" :min="1" placeholder="SL" style="width: 100%;" :controls="false" />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item :prop="`items.${index}.dosage`">
                <el-input v-model="item.dosage" placeholder="Liều lượng" />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item :prop="`items.${index}.usage_instruction`">
                <el-input v-model="item.usage_instruction" placeholder="Cách dùng" />
              </el-form-item>
            </el-col>
          </el-row>
        </div>

      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật Đơn Thuốc' : 'Lưu Đơn Thuốc' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, Delete, Close, Edit } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const prescriptions = ref([]);
const examinationsList = ref([]);
const medicinesList = ref([]);
const doctorsList = ref([]);

const loading = ref(false);
const searchQuery = ref('');
const totalPrescriptions = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({
  examination_id: '',
  notes: '',
  items: [
    { medicine_id: '', quantity: 1, dosage: '', usage_instruction: '' }
  ]
});

const rules = {
  examination_id: [{ required: true, message: 'Vui lòng chọn phiếu khám', trigger: 'change' }],
};

const getMedicineName = (medId) => {
  const med = medicinesList.value.find(m => m.id === medId);
  return med ? `${med.code} - ${med.name}` : `Thuốc #${medId}`;
};

const getDoctorName = (docId) => {
  const doc = doctorsList.value.find(d => d.id === docId);
  if (doc?.user?.name) return doc.user.name;
  if (doc?.name) return doc.name;
  return docId ? `Bác sĩ #${docId}` : 'N/A';
};

const fetchPrescriptions = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const res = await axios.get(`/api/prescriptions?page=${page}&search=${searchQuery.value}`);
    prescriptions.value = res.data.data || res.data;
    totalPrescriptions.value = res.data.meta?.total || res.data.total || prescriptions.value.length;
  } catch (error) { console.error(error); } finally { loading.value = false; }
};

const fetchRelations = async () => {
  try {
    const [exmRes, medRes, docRes] = await Promise.all([
      axios.get('/api/examinations'),
      axios.get('/api/medicines'),
      axios.get('/api/users?role_id=3')
    ]);
    examinationsList.value = exmRes.data.data || exmRes.data;
    medicinesList.value = medRes.data.data || medRes.data;
    doctorsList.value = docRes.data.data || docRes.data;
  } catch (error) {
    console.error('Không thể tải dữ liệu quan hệ:', error);
  }
};

const handleSearch = () => fetchPrescriptions(1);
const handlePageChange = (page) => fetchPrescriptions(page);

const openCreateDialog = () => {
  isEditMode.value = false;
  currentId.value = null;
  form.examination_id = '';
  form.notes = '';
  form.items = [{ medicine_id: '', quantity: 1, dosage: '', usage_instruction: '' }];
  dialogVisible.value = true;
};

const handleEdit = (row) => {
  isEditMode.value = true;
  currentId.value = row.id;
  form.examination_id = row.examination_id;
  form.notes = row.notes || '';
  
  // Clone lại danh sách thuốc đã lưu của đơn này
  const loadedItems = row.items || row.prescription_items || [];
  if (loadedItems.length > 0) {
    form.items = loadedItems.map(item => ({
      id: item.id, // Bắt buộc phải có id để backend nhận biết sửa hay thêm mới
      medicine_id: item.medicine_id,
      quantity: item.quantity,
      dosage: item.dosage,
      usage_instruction: item.usage_instruction
    }));
  } else {
    form.items = [{ medicine_id: '', quantity: 1, dosage: '', usage_instruction: '' }];
  }
  
  dialogVisible.value = true;
};

const addPrescriptionItem = () => {
  form.items.push({ medicine_id: '', quantity: 1, dosage: '', usage_instruction: '' });
};

const removePrescriptionItem = (index) => {
  form.items.splice(index, 1);
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      if (form.items.length === 0) return ElMessage.warning('Đơn thuốc phải có ít nhất 1 loại thuốc!');

      submitting.value = true;
      try {
        if (isEditMode.value) {
          // Gửi PUT request để update đơn thuốc và danh sách items
          await axios.put(`/api/prescriptions/${currentId.value}`, {
            notes: form.notes,
            items: form.items
          });
          ElMessage.success('Cập nhật đơn thuốc thành công!');
        } else {
          await axios.post('/api/prescriptions', {
            examination_id: form.examination_id,
            notes: form.notes,
            items: form.items
          });
          ElMessage.success('Kê đơn thuốc thành công!');
        }
        dialogVisible.value = false;
        fetchPrescriptions(currentPage.value);
      } catch (error) {
        ElMessage.error(error.response?.data?.message || 'Có lỗi xảy ra!');
      } finally { 
        submitting.value = false; 
      }
    }
  });
};

const handleDelete = (row) => {
  ElMessageBox.confirm(`Xóa đơn thuốc này sẽ khôi phục lại số lượng thuốc vào kho. Bạn có chắc chắn không?`, 'Cảnh báo', { type: 'warning' })
    .then(async () => {
      await axios.delete(`/api/prescriptions/${row.id}`);
      ElMessage.success('Đã xóa thành công và hoàn trả kho!');
      fetchPrescriptions(currentPage.value);
    }).catch(() => {});
};

onMounted(() => {
  fetchPrescriptions();
  fetchRelations();
});
</script>

<style scoped>
.expanded-detail {
  padding: 10px 30px 20px 30px;
  background-color: #f8fafc;
  border-radius: 8px;
}
.expanded-detail h4 {
  margin: 0 0 10px 0;
  color: #0284c7;
}
.prescription-item-row {
  background: #f1f5f9;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 12px;
  border: 1px solid #e2e8f0;
}
.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-weight: bold;
  font-size: 13px;
  color: #475569;
}
</style>