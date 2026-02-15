package com.zad.automation.flows;

import com.zad.automation.constants.FrameworkConstants;
import com.zad.automation.pages.*;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.time.Duration;

public class Phase1Flow {

    private final WebDriver driver;

    private String restaurantName;
    private String email;
    private final String password = "123456";

    public Phase1Flow(WebDriver driver) {
        this.driver = driver;
    }

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

    public void approveRestaurant() {

        driver.manage().deleteAllCookies();

        driver.get(FrameworkConstants.ADMIN_LOGIN_URL);

        AdminLoginPage login = new AdminLoginPage(driver);
        login.login("almustafa77sd@gmail.com", "Aa@123");

        new WebDriverWait(driver, Duration.ofSeconds(20))
                .until(ExpectedConditions.urlContains("index"));

        driver.get(FrameworkConstants.ADMIN_REQUESTS_URL);

        driver.navigate().refresh();

        AdminRequestsPage requests = new AdminRequestsPage(driver);

        requests.approveRestaurant(restaurantName);

        requests.logout();
    }


    public void loginAsRestaurant() {

        driver.get(FrameworkConstants.ADMIN_LOGIN_URL);

        AdminLoginPage login =
                new AdminLoginPage(driver);

        login.login(email, password);
    }


    public void manageProducts() {

        driver.get(FrameworkConstants.ADMIN_PRODUCTS_URL);

        AdminProductsPage page = new AdminProductsPage(driver);

        page.waitUntilPageLoaded();


        validateProductCreation(page);

        String productName =
                "Auto Product " + System.currentTimeMillis();

        page.addProduct(
                productName,
                "100",
                "10",
                "Test",
                "src/test/resources/Test.png"
        );

        String updatedName =
                page.editProduct(productName);

        page.deleteProduct(updatedName);

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
