<template>
  <div class="specialties-page">
    <el-card class="box-card" shadow="never">
      <div class="page-header">
        <h2 class="title">Quản lý Chuyên khoa</h2>
        <span class="subtitle">Thêm mới và quản lý các chuyên khoa khám bệnh của phòng khám</span>
      </div>

      <div class="toolbar">
        <el-button type="primary" class="btn-add" @click="openCreateDialog">
          <el-icon><Plus /></el-icon>
          <span>Thêm Chuyên khoa mới</span>
        </el-button>
        
        <el-input v-model="searchQuery" placeholder="Tìm kiếm chuyên khoa..." class="search-input" clearable @input="handleSearch">
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>
      </div>

      <el-table v-loading="loading" :data="specialties" class="custom-table" style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="name" label="Tên Chuyên Khoa" min-width="200" />
        <el-table-column prop="description" label="Mô tả" min-width="300" show-overflow-tooltip />
        <el-table-column label="Thao tác" width="140" align="center" fixed="right">
          <template #default="scope">
            <div class="action-buttons">
              <el-tooltip content="Chỉnh sửa" placement="top"><el-button type="primary" link @click="handleEdit(scope.row)"><el-icon :size="18"><Edit /></el-icon></el-button></el-tooltip>
              <el-tooltip content="Xóa" placement="top"><el-button type="danger" link @click="handleDelete(scope.row)"><el-icon :size="18"><Delete /></el-icon></el-button></el-tooltip>
            </div>
          </template>
        </el-table-column>
        <template #empty><el-empty description="Không có chuyên khoa nào" /></template>
      </el-table>
      
      <div class="pagination-wrapper">
        <el-pagination background layout="total, prev, pager, next, jumper" :total="totalSpecialties" :page-size="15" @current-change="handlePageChange" />
      </div>
    </el-card>

    <!-- DIALOG THÊM / SỬA -->
    <el-dialog v-model="dialogVisible" :title="isEditMode ? 'Chỉnh sửa Chuyên khoa' : 'Thêm Chuyên khoa mới'" width="450px" destroy-on-close>
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="Tên Chuyên Khoa" prop="name">
          <el-input v-model="form.name" placeholder="Nhập tên chuyên khoa..." size="large" />
        </el-form-item>
        <el-form-item label="Mô tả" prop="description">
          <el-input v-model="form.description" placeholder="Nhập mô tả chuyên khoa..." type="textarea" rows="3" size="large" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false" size="large">Hủy bỏ</el-button>
          <el-button type="primary" :loading="submitting" @click="submitForm" size="large" class="btn-add">
            {{ isEditMode ? 'Cập nhật' : 'Lưu chuyên khoa' }}
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

const specialties = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const totalSpecialties = ref(0);
const currentPage = ref(1);

const dialogVisible = ref(false);
const isEditMode = ref(false);
const currentId = ref(null);
const submitting = ref(false);
const formRef = ref(null);

const form = reactive({ name: '', description: '' });
const rules = { name: [{ required: true, message: 'Vui lòng nhập tên chuyên khoa', trigger: 'blur' }] };

const fetchSpecialties = async (page = 1) => {
  loading.value = true;
  currentPage.value = page;
  try {
    const res = await axios.get(`/api/specialties?page=${page}&search=${searchQuery.value}`);
    specialties.value = res.data.data || res.data;
    totalSpecialties.value = res.data.meta?.total || res.data.total || specialties.value.length;
  } catch (error) { console.error(error); } finally { loading.value = false; }
};

const handleSearch = () => fetchSpecialties(1);
const handlePageChange = (page) => fetchSpecialties(page);

const openCreateDialog = () => {
  isEditMode.value = false;
  currentId.value = null;
  form.name = ''; form.description = '';
  dialogVisible.value = true;
};

const handleEdit = (row) => {
  isEditMode.value = true;
  currentId.value = row.id;
  form.name = row.name;
  form.description = row.description || '';
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true;
      try {
        if (isEditMode.value) {
          await axios.put(`/api/specialties/${currentId.value}`, form);
          ElMessage.success('Cập nhật chuyên khoa thành công!');
        } else {
          await axios.post('/api/specialties', form);
          ElMessage.success('Thêm chuyên khoa mới thành công!');
        }
        dialogVisible.value = false;
        fetchSpecialties(currentPage.value);
      } catch (error) {
        ElMessage.error(error.response?.data?.message || 'Có lỗi xảy ra!');
      } finally { submitting.value = false; }
    }
  });
};

const handleDelete = (row) => {
  ElMessageBox.confirm(`Bạn có chắc chắn muốn xóa chuyên khoa ${row.name}?`, 'Cảnh báo', { type: 'warning' })
    .then(async () => {
      await axios.delete(`/api/specialties/${row.id}`);
      ElMessage.success('Đã xóa thành công!');
      fetchSpecialties(currentPage.value);
    }).catch(() => {});
};

onMounted(() => fetchSpecialties());
</script>