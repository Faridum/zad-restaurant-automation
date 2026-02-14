package com.zad.automation.base;

import com.zad.automation.constants.FrameworkConstants;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;

import java.time.Duration;

public class BasePage {

    protected WebDriver driver;
    protected WebDriverWait wait;

    public BasePage(WebDriver driver) {
        this.driver = driver;
        this.wait = new WebDriverWait(driver,
                Duration.ofSeconds(FrameworkConstants.EXPLICIT_WAIT));
    }

    protected void click(By locator) {
        wait.until(ExpectedConditions.elementToBeClickable(locator)).click();
    }

    protected void type(By locator, String text) {
        WebElement element = wait.until(
                ExpectedConditions.visibilityOfElementLocated(locator));
        element.clear();
        element.sendKeys(text);
    }

    protected String getAlertText() {
        wait.until(ExpectedConditions.alertIsPresent());
        return driver.switchTo().alert().getText();
    }

    protected boolean acceptAlertIfPresent() {

        try {
            wait.until(ExpectedConditions.alertIsPresent());
            driver.switchTo().alert().accept();
            return true;
        } catch (Exception e) {
            return false;
        }
    }

}
