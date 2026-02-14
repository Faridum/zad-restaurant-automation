package com.zad.automation.pages;

import com.zad.automation.base.BasePage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;

public class AdminLoginPage extends BasePage {

    private By email = By.name("email");
    private By password = By.name("password");
    private By loginBtn = By.cssSelector("button[type='submit']");

    public AdminLoginPage(WebDriver driver) {
        super(driver);
    }

    public void login(String user, String pass) {
        type(email, user);
        type(password, pass);
        click(loginBtn);
    }
}
