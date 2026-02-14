package com.zad.automation.pages;

import com.zad.automation.base.BasePage;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;

import java.io.File;

public class AdminProductsPage extends BasePage {

    // زر فتح المودال
    private By addProductBtn =
            By.cssSelector("button[data-bs-target='#addModal']");

    // الحقول
    private By nameInput = By.name("name");
    private By priceInput = By.name("price");
    private By quantityInput = By.name("quantity");
    private By descriptionInput = By.name("description");
    private By photoInput = By.id("addPhotoInput");

    // زر الحفظ داخل الفورم
    private By submitBtn =
            By.cssSelector("#addForm button[type='submit']");

    public AdminProductsPage(WebDriver driver) {
        super(driver);
    }

    public void addProduct(String name,
                           String price,
                           String quantity,
                           String description,
                           String imagePath) {

        // فتح المودال
        click(addProductBtn);

        // تعبئة البيانات
        type(nameInput, name);
        type(priceInput, price);
        type(quantityInput, quantity);
        type(descriptionInput, description);

        // رفع الصورة
        File file = new File(imagePath);
        driver.findElement(photoInput)
                .sendKeys(file.getAbsolutePath());

        // حفظ المنتج
        click(submitBtn);
    }

    public void deleteProduct(String productName) {

        By productRow = By.xpath(
                "//tr[td[contains(normalize-space(),'" + productName + "')]]"
        );

        // انتظر ظهور الصف أولاً
        wait.until(ExpectedConditions.visibilityOfElementLocated(productRow));

        By deleteBtn = By.xpath(
                "//tr[td[contains(normalize-space(),'" + productName + "')]]//button[contains(@class,'delete-btn')]"
        );

        wait.until(ExpectedConditions.elementToBeClickable(deleteBtn));
        click(deleteBtn);

        // انتظر ظهور نافذة التأكيد
        By confirmDeleteBtn = By.id("confirmDelete");
        wait.until(ExpectedConditions.visibilityOfElementLocated(confirmDeleteBtn));
        click(confirmDeleteBtn);

        // انتظر اختفاء الصف بعد الحذف
        wait.until(ExpectedConditions.invisibilityOfElementLocated(productRow));
    }

    // زر التعديل حسب اسم المنتج
    private By editButton(String productName) {
        return By.xpath(
                "//tr[td[contains(normalize-space(),'" + productName + "')]]//button[contains(@class,'edit-btn')]"
        );
    }

    // حقول التعديل
    private By editModal = By.id("editModal");

    private By editNameInput = By.id("edit-name");
    private By editPriceInput = By.id("edit-price");
    private By editDescriptionInput = By.id("edit-description");

    // هذا هو المهم 👇
    private By editQuantityInput =
            By.cssSelector("#editModal input[name='quantity']");

    private By editSubmitBtn =
            By.cssSelector("#editModal button[type='submit']");

    public String editProduct(String oldProductName) {

        String updatedName = oldProductName + "_Updated";

        click(editButton(oldProductName));

        // انتظر ظهور حقل الاسم الخاص بالتعديل
        wait.until(ExpectedConditions.visibilityOfElementLocated(editNameInput));

        type(editNameInput, updatedName);
        type(editPriceInput, "50");

        wait.until(ExpectedConditions.visibilityOfElementLocated(editQuantityInput));
        type(editQuantityInput, "5");

        type(editDescriptionInput, "Updated product");

        click(editSubmitBtn);

        By updatedRow = By.xpath(
                "//tr[td[contains(normalize-space(),'" + updatedName + "')]]"
        );

        wait.until(ExpectedConditions.visibilityOfElementLocated(updatedRow));

        return updatedName;
    }

    public boolean isValidationMessageDisplayed() {

        By validationMessage = By.cssSelector(".invalid-feedback, .error");

        try {
            wait.until(ExpectedConditions.visibilityOfElementLocated(validationMessage));
            return true;
        } catch (Exception e) {
            return false;
        }
    }
    public void waitUntilPageLoaded() {
        wait.until(ExpectedConditions.visibilityOfElementLocated(addProductBtn));
    }
    private By addModal = By.id("addModal");

    public boolean isAddModalStillOpen() {
        return wait.until(
                ExpectedConditions.visibilityOfElementLocated(addModal)
        ).isDisplayed();
    }
    private By cancelBtn =
            By.cssSelector("#addModal button[data-bs-dismiss='modal']");

    public void closeAddModal() {
        click(cancelBtn);
        wait.until(ExpectedConditions.invisibilityOfElementLocated(By.id("addModal")));
    }



}
