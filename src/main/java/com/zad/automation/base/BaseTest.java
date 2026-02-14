package com.zad.automation.base;

import com.zad.automation.driver.DriverFactory;
import org.openqa.selenium.WebDriver;
import org.testng.annotations.AfterClass;
import org.testng.annotations.BeforeClass;


public abstract class BaseTest {

    protected WebDriver driver;

    @BeforeClass
    public void setUp() {
        driver = DriverFactory.initDriver();
    }

    @AfterClass
    public void tearDown() {
        if (driver != null) {
            driver.quit();
        }
    }

}
