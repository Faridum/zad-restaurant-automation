package com.zad.automation.pages;

import com.zad.automation.base.BasePage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;

import java.io.File;

public class HomePage extends BasePage {

    private By openRegisterBtn = By.cssSelector("button[data-bs-target='#registerModal']");
    private By registerModal = By.id("registerModal");

    private By ownerName = By.name("owner_name");
    private By phone = By.name("phone");
    private By email = By.name("email");
    private By password = By.name("password");
    private By restaurantName = By.name("restaurant_name");
    private By address = By.name("address");
    private By proofsInput = By.id("proofsInput");
    private By submitBtn = By.cssSelector("#registerForm button[type='submit']");

    public HomePage(WebDriver driver) {
        super(driver);
    }

    public void openRegisterModal() {
        click(openRegisterBtn);
        wait.until(ExpectedConditions.attributeContains(registerModal, "class", "show"));
    }

    public void fillForm(String owner,
                         String phoneNum,
                         String emailValue,
                         String pass,
                         String restaurant,
                         String addr) {

        type(ownerName, owner);
        type(phone, phoneNum);
        type(email, emailValue);
        type(password, pass);
        type(restaurantName, restaurant);
        type(address, addr);
    }

    public void uploadProof(String path) {
        File file = new File(path);
        driver.findElement(proofsInput)
                .sendKeys(file.getAbsolutePath());
    }

    public void submit() {
        click(submitBtn);
    }

    public String getSuccessMessage() {
        return getAlertText();
    }

    public void confirmAlertIfPresent() {
        acceptAlertIfPresent();
    }
    public void waitForAlertAndAccept() {
        wait.until(ExpectedConditions.alertIsPresent());
        driver.switchTo().alert().accept();
    }

}
