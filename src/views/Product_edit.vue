<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายการสินค้า</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_product" role="button">Add+</a>
    </div>

    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อสินค้า</th>
          <th>รายละเอียด</th>
          <th>ราคา</th>
          <th>จำนวน</th>
          <th>รูปภาพ</th>
          <th>การจัดการ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.product_id">
          <td>{{ product.product_id }}</td>
          <td>{{ product.product_name }}</td>
          <td>{{ product.description }}</td>
          <td>{{ product.price }}</td>
          <td>{{ product.stock }}</td>
          <td>
            <img :src="'http://localhost/project_67711727/api_php/uploads/' + product.image" width="100" />
          </td>
          <td>
            <button class="btn btn-warning btn-sm me-2" @click="openEditModal(product)">แก้ไข</button>
            <button class="btn btn-danger btn-sm" @click="deleteProduct(product.product_id)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center"><p>กำลังโหลดข้อมูล...</p></div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <!-- Modal แก้ไขสินค้า -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">แก้ไขสินค้า</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateProduct">
              <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input v-model="editForm.product_name" type="text" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea v-model="editForm.description" class="form-control"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">ราคา</label>
                <input v-model="editForm.price" type="number" step="0.01" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">จำนวน</label>
                <input v-model="editForm.stock" type="number" class="form-control" required />
              </div>
              <div class="mb-3">
                <label class="form-label">รูปภาพ</label>
                <input type="file" @change="handleFileUpload" class="form-control" />
                <div v-if="editForm.image">
                  <p class="mt-2">รูปเดิม:</p>
                  <img :src="'http://localhost/project_67711727/api_php/uploads/' + editForm.image" width="100" />
                </div>
              </div>
              <div class="mb-3 text-center">
                <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { Modal } from "bootstrap";

export default {
  name: "ProductList",
  setup() {
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const editForm = ref({
      product_id: null,
      product_name: "",
      description: "",
      price: "",
      stock: "",
      image: ""
    });
    const newImageFile = ref(null);
    let modalInstance = null;

    const fetchProducts = async () => {
      try {
        const res = await fetch("http://localhost/project_67711727/api_php/product_crud.php");
        const data = await res.json();
        products.value = data.success ? data.data : [];
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    const openEditModal = (product) => {
      editForm.value = { ...product };
      newImageFile.value = null;
      const modalEl = document.getElementById("editModal");
      modalInstance = new Modal(modalEl);
      modalInstance.show();
    };

    const handleFileUpload = (event) => {
      newImageFile.value = event.target.files[0];
    };

    const updateProduct = async () => {
      const formData = new FormData();
      formData.append("action", "update");
      formData.append("product_id", editForm.value.product_id);
      formData.append("product_name", editForm.value.product_name);
      formData.append("description", editForm.value.description);
      formData.append("price", editForm.value.price);
      formData.append("stock", editForm.value.stock);
      if (newImageFile.value) {
        formData.append("image", newImageFile.value);
      }

      try {
        const res = await fetch("http://localhost/project_67711727/api_php/product_crud.php", {
          method: "POST",
          body: formData
        });
        const result = await res.json();
        if (result.message) {
          alert(result.message);
          fetchProducts();
          modalInstance.hide();
        } else if (result.error) {
          alert(result.error);
        }
      } catch (err) {
        alert(err.message);
      }
    };

    const deleteProduct = async (id) => {
      if (!confirm("คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?")) return;

      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("product_id", id);

      try {
        const res = await fetch("http://localhost/project_67711727/api_php/product_crud.php", {
          method: "POST",
          body: formData
        });
        const result = await res.json();
        if (result.message) {
          alert(result.message);
          products.value = products.value.filter(p => p.product_id !== id);
        } else if (result.error) {
          alert(result.error);
        }
      } catch (err) {
        alert(err.message);
      }
    };

    onMounted(fetchProducts);

    return {
      products,
      loading,
      error,
      editForm,
      openEditModal,
      handleFileUpload,
      updateProduct,
      deleteProduct
    };
  }
};
</script>
