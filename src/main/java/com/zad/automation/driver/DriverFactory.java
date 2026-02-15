package com.zad.automation.driver;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;

public final class DriverFactory {

    private DriverFactory(){}

    public static WebDriver createDriver() {

        ChromeOptions options = new ChromeOptions();

        String ci = System.getenv("CI");

        if (ci != null && ci.equalsIgnoreCase("true")) {

            options.addArguments("--headless=new");
            options.addArguments("--no-sandbox");
            options.addArguments("--disable-dev-shm-usage");
            options.addArguments("--window-size=1920,1080");

        } else {

            options.addArguments("--start-maximized");
        }

        return new ChromeDriver(options);
    }
}
