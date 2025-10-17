<template>
  <div class="container my-5">
    <h2 class="text-center mb-4">รายการสินค้า</h2>
    <div class="row">
      <div class="col-md-3" v-for="product in products" :key="product.product_id">
        <div class="card shadow-sm mb-4">
           <img
            :src="'http://localhost/project_67711727/api_php/uploads/' + product.image"
            width="70%"
            height="300"
            class="card-img-top"
            :alt="product.name"
          >
          <div class="card-body text-center">
            <h5 class="card-title">{{ product.product_name }}</h5>
            <p class="card-text">ราคา {{ product.price }} บาท [คงเหลือ {{ product.stock }}] </p>
            <button class="btn btn-primary">รายละเอียด</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";  // ดึงฟังก์ชันจาก Vue

export default {
  name: "ProductList",  // ชื่อ component

  setup() {
    // ตัวแปร reactive
    const products = ref([]);    // เก็บข้อมูลสินค้า
    const loading = ref(true);   // สถานะกำลังโหลด
    const error = ref(null);     // เก็บข้อความ error (ถ้ามี)

    // ฟังก์ชันดึงข้อมูลจาก API (method GET)
    const fetchProducts = async () => {
      try {
        // เรียก API จากไฟล์ show_product.php
        const response = await fetch("http://localhost/project_67711727/api_php/show_product.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json"
          }
        });

        // ถ้า response ไม่ปกติ (เช่น 404, 500) ให้โยน error ออกมา
        if (!response.ok) {
          throw new Error("ไม่สามารถดึงข้อมูลได้");
        }

        // แปลงข้อมูลเป็น JSON
        const result = await response.json();

        // ถ้า API ส่ง success = true → นำข้อมูลใส่ products
        if (result.success) {
          products.value = result.data;
        } else {
          // ถ้า API ส่ง success = false → แสดงข้อความ error
          error.value = result.message;
        }

      } catch (err) {
        // กรณีเกิด error เช่น server ล่ม หรือ network มีปัญหา
        error.value = err.message;
      } finally {
        // ไม่ว่าจะสำเร็จหรือ fail ก็ set loading = false
        loading.value = false;
      }
    };

    // เรียกฟังก์ชัน fetchProducts() เมื่อ component ถูก mount
    onMounted(() => {
      fetchProducts();
    });

    // return ค่าออกไปให้ template ใช้ได้
    return {
      products,
      loading,
      error
    };
  }
};
</script>


