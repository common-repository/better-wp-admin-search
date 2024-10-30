import "../../css/src/main.scss";
import PaginatedResults from "./paginatedResults";

import SearchForm from "./searchForm";
import FilteredResults from "./filteredResults";

window.addEventListener("DOMContentLoaded", () => {
  new SearchForm();
  const paginatedResults = new PaginatedResults();
  paginatedResults.initEvents();
  new FilteredResults();
});

