package com.zad.automation.flows;

import com.zad.automation.constants.FrameworkConstants;
import com.zad.automation.pages.*;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;

public class Phase1Flow {

    private static final Duration DEFAULT_TIMEOUT = Duration.ofSeconds(20);
    private static final String DEFAULT_PASSWORD = "123456";
    private static final String TEST_IMAGE_PATH = "src/test/resources/Test.png";
    private static final String DEFAULT_CITY = "Damascus";

    private final WebDriver driver;
    private final WebDriverWait wait;

    private String restaurantName;
    private String email;

    public Phase1Flow(WebDriver driver) {
        this.driver = driver;
        this.wait = new WebDriverWait(driver, DEFAULT_TIMEOUT);
    }

    /* ============================================================
                            REGISTRATION
       ============================================================ */

    public void registerRestaurant() {
        generateRestaurantData();
        openHomePage();
        submitRegistrationForm();
    }

    private void generateRestaurantData() {
        long timestamp = System.currentTimeMillis();
        restaurantName = "AutoRestaurant_" + timestamp;
        email = "auto" + timestamp + "@test.com";
    }

    private void openHomePage() {
        driver.get(FrameworkConstants.BASE_URL);
    }

    private void submitRegistrationForm() {
        HomePage home = new HomePage(driver);

        home.openRegisterModal();

        home.fillForm(
                "Automation User",
                generateRandomPhone(),
                email,
                DEFAULT_PASSWORD,
                restaurantName,
                DEFAULT_CITY
        );

        home.uploadProof(TEST_IMAGE_PATH);
        home.submit();
        home.waitForAlertAndAccept();
    }

    private String generateRandomPhone() {
        return "09111" + (int) (Math.random() * 10_000_000);
    }

    /* ============================================================
                            APPROVAL (ADMIN)
       ============================================================ */

    public void approveRestaurant() {
        clearSession();
        loginAsAdmin();
        openRequestsPage();
        approvePendingRestaurant();
        logoutAdmin();
    }

    private void clearSession() {
        driver.manage().deleteAllCookies();
    }

    private void loginAsAdmin() {
        driver.get(FrameworkConstants.ADMIN_LOGIN_URL);

        AdminLoginPage login = new AdminLoginPage(driver);
        login.login(
                FrameworkConstants.ADMIN_EMAIL,
                FrameworkConstants.ADMIN_PASSWORD
        );

        wait.until(ExpectedConditions.urlContains("index"));
    }

    private void openRequestsPage() {
        driver.get(FrameworkConstants.ADMIN_REQUESTS_URL);
        driver.navigate().refresh();
    }

    private void approvePendingRestaurant() {
        AdminRequestsPage requests = new AdminRequestsPage(driver);
        requests.approveRestaurant(restaurantName);
    }

    private void logoutAdmin() {
        new AdminRequestsPage(driver).logout();
    }

    /* ============================================================
                            RESTAURANT LOGIN
       ============================================================ */

    public void loginAsRestaurant() {
        driver.get(FrameworkConstants.ADMIN_LOGIN_URL);

        AdminLoginPage login = new AdminLoginPage(driver);
        login.login(email, DEFAULT_PASSWORD);

        wait.until(ExpectedConditions.urlContains("dashboard"));
    }

    /* ============================================================
                            PRODUCT MANAGEMENT
       ============================================================ */

    public void manageProducts() {
        openProductsPage();
        validateInvalidProductSubmission();
        performFullProductLifecycle();
    }

    private void openProductsPage() {
        driver.get(FrameworkConstants.ADMIN_PRODUCTS_URL);
        wait.until(ExpectedConditions.urlContains("products"));
        new AdminProductsPage(driver).waitUntilPageLoaded();
    }

    private void validateInvalidProductSubmission() {
        AdminProductsPage page = new AdminProductsPage(driver);

        page.addProduct(
                "",
                "100",
                "10",
                "Invalid",
                TEST_IMAGE_PATH
        );

        if (!page.isAddModalStillOpen()) {
            throw new AssertionError("Product form accepted invalid input.");
        }

        page.closeAddModal();
    }

    private void performFullProductLifecycle() {
        AdminProductsPage page = new AdminProductsPage(driver);

        String productName = "AutoProduct_" + System.currentTimeMillis();

        page.addProduct(productName, "100", "10", "Test", TEST_IMAGE_PATH);

        String updatedName = page.editProduct(productName);

        page.deleteProduct(updatedName);

        page.addProduct("Final Product", "50", "5", "Done", TEST_IMAGE_PATH);
    }
}
