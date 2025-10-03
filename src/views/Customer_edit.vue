<template>
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อลูกค้า</h2>
    
    <div class="mb-3">
      <a class="btn btn-primary" href="/add_customer" role="button">Add+</a>
    </div>

    <!-- ตารางแสดงข้อมูลลูกค้า -->
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>เบอร์โทร</th>
          <th>ชื่อผู้ใช้</th>
          <th>แก้ไข/ลบ</th>
          
        </tr>
      </thead>
      <tbody>
        <tr v-for="customer in customers" :key="customer.customer_id">
          <td>{{ customer.customer_id }}</td>
          <td>{{ customer.firstName }}</td>
          <td>{{ customer.lastName }}</td>
          <td>{{ customer.phone }}</td>
          <td>{{ customer.username }}</td>
          <td>
            <!-- เพิ่ม ปุ่มแก้ไข -->
            <button class="btn btn-warning btn-sm" @click="openEditModal(customer)"><i class="fa-solid fa-pen-to-square"></i> </button> |      
            <!-- ปุ่มลบ -->
            <button class="btn btn-danger btn-sm" @click="deleteCustomer(customer.customer_id)"><i class="fa-solid fa-trash"></i> </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

    <!-- เพิ่ม Modal แก้ไขข้อมูล -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">แก้ไขข้อมูลลูกค้า</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateCustomer">
              <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input v-model="editCustomer.firstName" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล</label>
                <input v-model="editCustomer.lastName" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input v-model="editCustomer.phone" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input v-model="editCustomer.username" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">รหัสผ่าน (เว้นว่างหากไม่เปลี่ยน)</label>
                <input v-model="editCustomer.password" type="password" class="form-control">
              </div>
              <div class="mb-3 text-center">
                <button type="submit" class="btn btn-success">บันทึก</button>
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
import { Modal } from "bootstrap";   // เพิ่ม ✅ import Modal class

export default {
  name: "CustomerList",
  setup() {
    const customers = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const editCustomer = ref({});   //เพิ่ม
    let editModal;                  //เพิ่ม

    const fetchCustomers = async () => {
      try {
        const response = await fetch("http://localhost/project_67711727/api_php/customer_crud.php", {
          method: "GET",
          headers: { "Content-Type": "application/json" }
        });

        if (!response.ok) throw new Error("ไม่สามารถดึงข้อมูลได้");

        const result = await response.json();
        if (result.success) {
          customers.value = result.data;
        } else {
          error.value = result.message;
        }
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchCustomers();
      const modalEl = document.getElementById("editModal");     //เพิ่ม
      editModal = new Modal(modalEl);   // เพิ่ม ✅ ใช้ Modal ที่ import มา
    });

//เพิ่ม เปิด Popup Modal ***
    const openEditModal = (customer) => {
      editCustomer.value = { ...customer };
      editModal.show();
    };
// เพิ่มฟังก์ชั่นการแก้ไขข้อมูล ***
    const updateCustomer = async () => {
      try {
        const response = await fetch("http://localhost/project_67711727/api_php/customer_crud.php", {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(editCustomer.value)
        });

        const result = await response.json();

        if (result.success) {
          const index = customers.value.findIndex(c => c.customer_id === editCustomer.value.customer_id);
          if (index !== -1) customers.value[index] = { ...editCustomer.value };

          alert("แก้ไขข้อมูลสำเร็จ");
          editModal.hide();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

//ฟังก์ชั่นการลบข้อมูล ***
    const deleteCustomer = async (id) => {
      if (!confirm("คุณต้องการลบข้อมูลนี้ใช่หรือไม่?")) return;

      try {
        const response = await fetch("http://localhost/project_67711727/api_php/customer_crud.php", {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ customer_id: id })
        });

        const result = await response.json();

        if (result.success) {
          customers.value = customers.value.filter(c => c.customer_id !== id);
          alert(result.message);
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    return {
      customers,
      loading,
      error,
      deleteCustomer,
      
      editCustomer,  //เพิ่ม
      openEditModal,  //เพิ่ม
      updateCustomer  //เพิ่ม
    };
  }
};
</script>
