import PaginatedResults from "./paginatedResults";

export default class FilteredResults {
  constructor() {
    this.filters = document.querySelectorAll("a.filter-handler");
    this.filtersWrapper = document.querySelector(".bwpas-filter-results");
    this.init();
  }
  /**
   * Actions on page load
   */
  init = () => {
    if (this.filters.length) {
      this.filters.forEach((filter) =>
        filter.addEventListener("click", (e) => this.filterResults(e))
      );
    }
  };
  filterResults = (e) => {
    e.preventDefault();
    const target = e.target.classList.contains("filter-handler")
      ? e.target
      : e.target.closest(".filter-handler");

    if (this.filtersWrapper) {
      const current = this.filtersWrapper.querySelector("a.current");
      if (current) {
        current.classList.remove("current");
      }
    }
    target.classList.add("current");
    // send request for this posttype only - get data as in pagination, update pagination details, display/hide pagination based on filter results
    const paginatedResults = new PaginatedResults();
    paginatedResults.handlePaginatedResults(e, "filter"); 
  };
}
