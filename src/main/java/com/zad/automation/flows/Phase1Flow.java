package com.zad.automation.flows;

import com.zad.automation.constants.FrameworkConstants;
import com.zad.automation.pages.*;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;

public class Phase1Flow {

    private final WebDriver driver;

    private String restaurantName;
    private String email;
    private final String password = "123456";

    public Phase1Flow(WebDriver driver) {
        this.driver = driver;
    }

    // =========================
    // STEP 1: Registration
    // =========================
    public void registerRestaurant() {

        driver.get(FrameworkConstants.BASE_URL);

        restaurantName =
                "AutoRestaurant_" + System.currentTimeMillis();

        email =
                "auto" + System.currentTimeMillis() + "@test.com";

        HomePage home = new HomePage(driver);

        home.openRegisterModal();

        home.fillForm(
                "Automation User",
                "09111" + (int)(Math.random() * 10000000),
                email,
                password,
                restaurantName,
                "Damascus"
        );

        home.uploadProof("src/test/resources/Test.png");
        home.submit();
        home.waitForAlertAndAccept();
    }

    // =========================
    // STEP 2: Admin Approval
    // =========================
    public void approveRestaurant() {
        driver.manage().deleteAllCookies();

        driver.get(FrameworkConstants.ADMIN_LOGIN_URL);

        AdminLoginPage login = new AdminLoginPage(driver);
        login.login("almustafa77sd@gmail.com", "Aa@123");

        driver.get(FrameworkConstants.ADMIN_REQUESTS_URL);

        AdminRequestsPage requests =
                new AdminRequestsPage(driver);

        requests.approveRestaurant(restaurantName);
        requests.logout();
    }

    // =========================
    // STEP 3: Login as Restaurant
    // =========================
    public void loginAsRestaurant() {

        driver.get(FrameworkConstants.ADMIN_LOGIN_URL);

        AdminLoginPage login =
                new AdminLoginPage(driver);

        login.login(email, password);
    }

    // =========================
    // STEP 4: Product Management
    // =========================
    public void manageProducts() {

        driver.get(FrameworkConstants.ADMIN_PRODUCTS_URL);

        AdminProductsPage page = new AdminProductsPage(driver);

// انتظر حتى يظهر زر إضافة المنتج
        page.waitUntilPageLoaded();


        // 1️⃣ Validation Test (Negative Scenario)
        validateProductCreation(page);

        // 2️⃣ Add Product (Positive Scenario)
        String productName =
                "Auto Product " + System.currentTimeMillis();

        page.addProduct(
                productName,
                "100",
                "10",
                "Test",
                "src/test/resources/Test.png"
        );

        // 3️⃣ Edit
        String updatedName =
                page.editProduct(productName);

        // 4️⃣ Delete
        page.deleteProduct(updatedName);

        // 5️⃣ Add Final Product
        page.addProduct(
                "Final Product",
                "50",
                "5",
                "Done",
                "src/test/resources/Test.png"
        );
    }

    private void validateProductCreation(AdminProductsPage page) {

        page.addProduct(
                "",     // ❌ Empty name
                "100",
                "10",
                "Invalid",
                "src/test/resources/Test.png"
        );

        if (!page.isAddModalStillOpen()) {
            throw new AssertionError("Form submitted despite invalid input");
        }

        // أغلق المودال بعد التحقق
        page.closeAddModal();
    }



}
