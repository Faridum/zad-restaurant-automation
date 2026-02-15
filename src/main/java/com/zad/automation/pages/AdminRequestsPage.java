package com.zad.automation.pages;

import com.zad.automation.base.BasePage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;

public class AdminRequestsPage extends BasePage {

    public AdminRequestsPage(WebDriver driver) {
        super(driver);
    }

    public void approveRestaurant(String restaurantName) {

        By row = By.xpath(
                "//tr[td[contains(normalize-space(),'" + restaurantName + "')]]"
        );

        wait.until(ExpectedConditions.visibilityOfElementLocated(row));

        By approveBtn = By.xpath(
                "//tr[td[contains(normalize-space(),'" + restaurantName + "')]]//button[contains(@class,'approve')]"
        );

        wait.until(ExpectedConditions.elementToBeClickable(approveBtn));
        click(approveBtn);

        wait.until(ExpectedConditions.visibilityOfElementLocated(By.id("confirmModal")));
        click(By.id("confirmYes"));
    }

    private By logoutBtn = By.cssSelector("a[href='logout.php']");

    public void logout() {
        click(logoutBtn);
    }

}
