package com.zad.automation.driver;

import io.github.bonigarcia.wdm.WebDriverManager;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;

public class DriverFactory {

    public static WebDriver initDriver() {

        WebDriverManager.chromedriver().setup();

        WebDriver driver = new ChromeDriver();
        driver.manage().window().maximize();

        return driver;
    }
}
