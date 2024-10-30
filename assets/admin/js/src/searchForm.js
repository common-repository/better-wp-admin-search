export default class SearchForm {
  constructor() {
    this.optBulkHandler = document.querySelectorAll(
      ".bwpas-wrap .opt-bulk-handler"
    );
    this.submitSearchBtn = document.getElementById("bwpas-submit");
    this.init();
  }
  /**
   * Actions on page load
   */
  init = () => {
    if (this.optBulkHandler.length > 0) {
      this.optBulkHandler.forEach((opt) => {
        opt.addEventListener("click", (e) =>
          this.bulkPostTypeOptionsHandler(e)
        );
      });
    }
    if (this.submitSearchBtn) {
      this.submitSearchBtn.addEventListener("click", (e) => this.deactivate(e));
      document.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
          if (document.hasFocus()) {
            const activeEl = document.activeElement;
            if (activeEl.hasAttribute("id")) {
              if (activeEl.id === "current-page-input") {
                return;
              }
            }
          }
          e.preventDefault();
          this.submitSearchBtn.click();
        }
      });
    }
  };
  /**
   * Bulk selects/deselects
   * post types options
   * Limited to the
   * post types listed
   * in the handler parent-wrapper
   */
  bulkPostTypeOptionsHandler = (e) => {
    const postTypesWrapper = e.target.closest(".post-types-wrapper");
    const postTypesOptions = postTypesWrapper.querySelectorAll(
      ".bwpas-wrap .post-types-opt li input"
    );
    if (e.target.dataset.select) {
      if (e.target.dataset.select === "select") {
        // select this e.target's block post types only
        if (postTypesOptions.length) {
          postTypesOptions.forEach((opt) => {
            if (!opt.checked) opt.checked = true;
          });
        }
      }
      if (e.target.dataset.select === "clear") {
        // deselect this e.target's block post types only
        if (postTypesOptions.length) {
          postTypesOptions.forEach((opt) => {
            if (opt.checked) opt.checked = false;
          });
        }
      }
    }
  };
  /**
   * Indicates that the
   * search form has been submitted
   * Deactivates the submit(search) button
   * while waiting for the response.
   * @param {*} e
   */
  deactivate = (e) => {
    e.target.classList.add("submitting");
    const wrapper = e.target.closest(".submit");
    if (wrapper) {
      const loader = wrapper.querySelector(".loader");
      if (loader) {
        if (loader.classList.contains("hidden-loader")) {
          loader.classList.remove("hidden-loader");
        }
      }
    }
  };
}
