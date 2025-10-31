<template>
 <nav class="navbar navbar-expand-lg bg-dark border-bottom border-body" data-bs-theme="dark">
  <div class="container">
    <a class="navbar-brand" href="#">Navbar</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

         <!-- แสดงเฉพาะเมื่อเข้าสู่ระบบแล้ว -->
        <template v-if="isLoggedIn">
        
       <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Product
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/product">Product</a></li>
            <li><a class="dropdown-item" href="/showproduct">ShowProduct</a></li>
            <li><a class="dropdown-item" href="/product_edit">Product Edit</a></li>
          </ul>
        </li>
          <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/customer_edit">Customer Edit</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/employee">Employee</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/student">Student</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" @click="logout">logout</a>
        </li>
        </template>

        <!-- แสดงเฉพาะเมื่อยังไม่ได้เข้าสู่ระบบ -->
        <template v-else>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/customer">Customer</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            login
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/login_customer">login</a></li>
            <li><a class="dropdown-item" href="#" @click="logout">Logout</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/add_customer">Register</a></li>
          </ul>
        </li>
        </template>
        <li class="nav-item">
          <a class="nav-link" href="/about">About</a>
        </li>
      </ul> 
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search"/>
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
      
    </div>
  </div>
</nav>



  
  <router-view/>

</template>

<script>
export default {
  name: "Navbar",
  data() {
    return {
      isLoggedIn: false,
    };
  },
  mounted() {
    // ตรวจสอบสถานะเมื่อโหลดหน้า
    this.checkLogin();
  },
  methods: {
    checkLogin() {
      this.isLoggedIn = localStorage.getItem("customerLogin") === "true";
    },
    logout() {
      if (confirm("ต้องการออกจากระบบหรือไม่?")) {
        // เคลียร์ข้อมูลทั้งหมดที่เกี่ยวข้องกับการล็อกอิน
        localStorage.removeItem("customerLogin");
        localStorage.removeItem("username");
        localStorage.removeItem("token");
        this.isLoggedIn = false;

        // กลับไปหน้าเมนูหลัก
        this.$router.push("/");
      }
    },
  },
  watch: {
    // เมื่อเปลี่ยนเส้นทาง ให้ตรวจสอบสถานะการล็อกอินใหม่
    $route() {
      this.checkLogin();
    },
  },
};
</script>


<style scoped>
.navbar {
  background-color: rgb(28, 34, 39) !important;
}
.nav-link {
  color: white !important;
  font-weight: 500;
}
.nav-link:hover {
  text-decoration: underline;
}
</style>