<template>
  <div class="medicines-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Thuốc </h2>
        <span class="subtitle">Quản lý danh mục, số lượng tồn kho và đơn giá của các loại thuốc</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Thêm Thuốc mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm tên thuốc, mã thuốc..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="medicines" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="ID" width="70" align="center" />
        <el-table-column prop="code" label="Mã Thuốc" width="130">
          <template #default="scope">
            <strong>{{ scope.row.code }}</strong>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="Tên Thuốc" min-width="220" />
        <el-table-column prop="unit" label="Đơn vị tính" width="120" align="center">
          <template #default="scope">
            <el-tag type="info" effect="plain">{{ scope.row.unit }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="price" label="Đơn giá" width="150" align="right">
          <template #default="scope">
            <span style="color: #ef4444; font-weight: 600;">{{ formatCurrency(scope.row.price) }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="stock" label="Tồn kho" width="120" align="center">
          <template #default="scope">
            <el-tag :type="scope.row.stock > 10 ? 'success' : 'danger'">
              {{ scope.row.stock }} {{ scope.row.unit }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Chỉnh sửa" placement="top"><el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button></el-tooltip>
              <el-tooltip content="Xóa" placement="top"><el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button></el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Không có dữ liệu thuốc nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalMedicines" :page-size="15" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG THÊM / SỬA -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh sửa thông tin Thuốc' : 'Thêm Thuốc mới'" width="500px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Mã thuốc" prop="code">
              <el-input v-model="form.code" placeholder="VD: MED-001..." size="large" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Đơn vị tính" prop="unit">
              <el-select v-model="form.unit" placeholder="Chọn đơn vị..." size="large" style="width: 100%;">
                <el-option label="Viên" value="Viên" />
                <el-option label="Vỉ" value="Vỉ" />
                <el-option label="Hộp" value="Hộp" />
                <el-option label="Chai" value="Chai" />
                <el-option label="Ống" value="Ống" />
                <el-option label="Gói" value="Gói" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Tên thuốc" prop="name">
          <el-input v-model="form.name" placeholder="Nhập tên thuốc..." size="large" />
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Đơn giá (VNĐ)" prop="price">
              <el-input-number v-model="form.price" :min="0" :step="1000" size="large" style="width: 100%;" :controls="false" placeholder="Nhập giá bán..." />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Số lượng tồn kho" prop="stock">
              <el-input-number v-model="form.stock" :min="0" size="large" style="width: 100%;" placeholder="Nhập số lượng..." />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật' : 'Lưu thông tin' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Plus, Search, Edit, Delete } from '@element-plus/icons-vue';
import axios from 'axios';
import { ElMessage, ElMessageBox } from 'element-plus';

const medicines = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const totalMedicines = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({ code: '', name: '', unit: '', price: null, stock: null });
const rules = {
  code: [{ required: true, message: 'Vui lòng nhập mã thuốc', trigger: 'blur' }],
  name: [{ required: true, message: 'Vui lòng nhập tên thuốc', trigger: 'blur' }],
  unit: [{ required: true, message: 'Vui lòng chọn đơn vị tính', trigger: 'change' }],
  price: [{ required: true, message: 'Vui lòng nhập đơn giá', trigger: 'blur' }],
  stock: [{ required: true, message: 'Vui lòng nhập số lượng tồn', trigger: 'blur' }]
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const fetchMedicines = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const res = await axios.get(`/api/medicines?page=${page}&search=${searchQuery.value}`);
    medicines.value = res.data.data || res.data;
    totalMedicines.value = res.data.meta?.total || res.data.total || medicines.value.length;
  } catch (error) { console.error(error); } finally { loading.value = false; }
};

const handleSearch = () => fetchMedicines(1);
const handlePageChange = (page) => fetchMedicines(page);

const openCreateDialog = () => {
  isEditMode.value = false;
  currentId.value = null;
  form.code = ''; form.name = ''; form.unit = ''; form.price = null; form.stock = null;
  dialogVisible.value = true;
};

const handleEdit = (row) => {
  isEditMode.value = true;
  currentId.value = row.id;
  form.code = row.code;
  form.name = row.name;
  form.unit = row.unit;
  form.price = row.price;
  form.stock = row.stock;
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        if (isEditMode.value) {
          await axios.put(`/api/medicines/${currentId.value}`, form);
          ElMessage.success('Cập nhật thuốc thành công!');
        } else {
          await axios.post('/api/medicines', form);
          ElMessage.success('Thêm thuốc mới thành công!');
        }
        dialogVisible.value = false;
        fetchMedicines(currentPage.value);
      } catch (error) {
        ElMessage.error(error.response?.data?.message || 'Có lỗi xảy ra!');
      } finally { submitting.value = false; }
    }
  });
};

const handleDelete = (row) => {
  ElMessageBox.confirm(`Bạn có chắc chắn muốn xóa thuốc ${row.name}?`, 'Cảnh báo', { type: 'warning' })
    .then(async () => {
      await axios.delete(`/api/medicines/${row.id}`);
      ElMessage.success('Đã xóa thành công!');
      fetchMedicines(currentPage.value);
    }).catch(() => {});
};

onMounted(() => fetchMedicines());
</script>