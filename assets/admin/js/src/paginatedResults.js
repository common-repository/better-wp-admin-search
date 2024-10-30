export default class PaginatedResults {
  constructor() {
    this.paginationLinks = document.querySelectorAll(".bwpas-wrap a.button");
    this.navDirections = {
      firstPage: "first-page",
      lastPage: "last-page",
      prevPage: "prev-page",
      nextPage: "next-page",
      inputPage: "input-page",
    };
    this.requestTypes = {
      link: "link",
      input: "input",
      filter: "filter",
    };
    this.paginationState = {
      show: "show",
      hide: "hide",
    };
    this.pageNumInput = document.querySelector(
      ".bwpas-wrap #current-page-input"
    );
  }
  /**
   * Attach events
   * on page load
   */
  initEvents = () => {
    if (this.paginationLinks) {
      this.paginationLinks.forEach((link) => {
        link.addEventListener("click", (e) =>
          this.handlePaginatedResults(e, this.requestTypes.link)
        );
      });
    }
    if (this.pageNumInput) {
      this.pageNumInput.addEventListener("keyup", (e) =>
        this.handlePaginatedResults(e, this.requestTypes.input)
      );
    }
  };
  /**
   * Get paginated results
   * entry point
   *
   * @param {*} e
   * @param {String} requestType
   */
  handlePaginatedResults = (e, requestType) => {
    e.preventDefault();
    e.stopPropagation();
    let pageTo, target, wrapper, postTypes;
    switch (requestType) {
      case this.requestTypes.link:
        target = e.target.classList.contains("button")
          ? e.target
          : e.target.closest(".button");
        wrapper = target.closest(".search-results-wrapper");
        postTypes = JSON.parse(wrapper.dataset.postTypes);
        pageTo = target.dataset.page;
        this.getPaginatedResult(
          pageTo,
          target,
          postTypes,
          this.requestTypes.link
        );
        break;
      case this.requestTypes.input:
        if (e.key === "Enter") {
          pageTo = e.target.value;
          wrapper = e.target.closest(".search-results-wrapper");
          postTypes = JSON.parse(wrapper.dataset.postTypes);
          if (pageTo > 0 && pageTo <= +e.target.dataset.total) {
            this.getPaginatedResult(
              pageTo,
              e.target,
              postTypes,
              this.requestTypes.input
            );
          }
        }
        break;
      case this.requestTypes.filter:
        target = e.target.classList.contains("filter-handler")
          ? e.target
          : e.target.closest(".filter-handler");
        // land on page 1
        pageTo = 1;
        postTypes = JSON.parse(target.dataset.postTypes);
        this.getPaginatedResult(
          pageTo,
          e.target,
          postTypes,
          this.requestTypes.filter
        );
    }
  };
  /**
   * REST request to retrieve paginated results
   * and updates pagination data on success
   *
   * @param {Number} page
   * @param {HTMLAnchorElement|HTMLInputElement} target
   * @param {Array} postTypes
   * @param {String} requestType
   * @return void
   */
  getPaginatedResult = async (page, target, postTypes, requestType) => {
    const searchString = document.getElementById(
      "bwpas-input-search-hidden"
    ).value; // @TODO validate not empty!
    const casensitive = document.getElementById(
      "bwpas-casesensitive-search"
    ).value; // @TODO validate
    const data = {
      page: page,
      post_types: postTypes,
      search_str: searchString,
      casesensitive: casensitive,
    };
    const url = `${bwpasApiSettings.api_root}bwpas/v1/bwpa-search`;
    try {
      const response = await fetch(url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-WP-Nonce": `${bwpasApiSettings.nonce}`,
        },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      const { result_list, total_posts, total_pages } = JSON.parse(result);

      if (total_pages > 1) {
        this.togglePagination(this.paginationState.show);
        const newPaginationData = {
          target,
          page,
          requestType,
          pagesTotal: total_pages,
          postsTotal: total_posts,
        };
        if (this.requestTypes.filter == requestType) {
          this.updatePostTypesSearch(target, postTypes);
        }
        this.updatePaginationData(newPaginationData);
      } else {
        this.togglePagination(this.paginationState.hide);
      }
      this.displayPaginatedResultList(result_list);
    } catch (err) {
      console.log("Fetch Error :-S", err);
    }
  };
  /**
   * Lists paginated result
   * in the result table
   * @param {Array} paginatedResultList
   * @returns void
   */
  displayPaginatedResultList = (paginatedResultList) => {
    let paginatedResultRows = "";
    if (paginatedResultList.length) {
      paginatedResultList.forEach((result) => {
        paginatedResultRows += this.displayResultRow(result);
      });
    }
    const resultsListContainer = document.querySelector(".results-table tbody");
    resultsListContainer.innerHTML = paginatedResultRows;
  };
  /**
   * Selects type of markup to display
   * @param {Object} data
   * @returns void
   */
  displayResultRow = (data) => {
    switch (data.post_type) {
      case "attachment":
        return this.displayResultRowMedia(data);

      default:
        return this.displayResultRowDefault(data);
    }
  };
  /**
   * Default markup row
   * to display the result
   * @param {Object} data
   * @returns void
   */
  displayResultRowDefault = (data) => {
    let output = "";
    output += "<tr><td><div>";
    output += `${data.title_output}`;
    output += `</div><div class="actions">`;
    output += `${data.actions_output}`;
    output += `</div></td>`;
    output += `<td>${data.post_name_output}</td>`;
    output += `<td>${data.post_author_display_name_output}</td>`;
    output += `<td>${data.post_categories_output}</td>`;
    output += `<td><div>${data.post_status_output}</div>`;
    output += `<div class="post-date">${data.post_date_created_output}</div></td></tr>`;
    return output;
  };
  /**
   * Media type markup row
   * for a single result
   * @param {Object} data
   * @returns void
   */
  displayResultRowMedia = (data) => {
    let output = "";
    output += `<tr><td>`;
    output += `<div class="flex"><div class="col"><div class="attachment-title">`;
    output += `${data.title_output}`;
    output += `</div><div class="actions">`;
    output += `${data.actions_output}`;
    output += `</div></div><div class="attachment-details col">`;
    output += `${data.post_attachment_details}`;
    output += `</div></div></td>`;
    output += `<td>${data.post_name_output}</td>`;
    output += `<td>${data.post_author_display_name_output}</td>`;
    output += `<td>${data.post_categories_output}</td>`;
    output += `<td><div>${data.post_status_output}</div>`;
    output += `<div class="post-date">${data.post_date_created_output}</div></td></tr>`;
    return output;
  };
  /**
   * Updates pagination datasets
   *
   * @param {Object} args data needed to change the pagination state
   * @returns void
   */
  updatePaginationData = (args) => {
    const { target, page, requestType, postsTotal, pagesTotal } = args;
    const currentPageInput = document.getElementById("current-page-input");
    if (currentPageInput) {
      currentPageInput.value = page;
    }
    const currentPageText = document.getElementById("current-page-text");
    if (currentPageText) {
      currentPageText.innerText = page;
    }
    const navDirection = this.getNavDirection(target, requestType);
    const paginationWrappers = document.querySelectorAll(
      ".bwpas-wrap .tablenav-pages"
    );
    if (paginationWrappers.length) {
      paginationWrappers.forEach((wrap) => {
        const setDataPageArgs = {
          wrap,
          navDirection,
          page,
          requestType,
          postsTotal,
          pagesTotal,
        };
        this.setDataPage(setDataPageArgs);
      });
    }
  };
  /**
   * Gets the pagination
   * navigation directions
   *
   * @param {HTMLAnchorElement|HTMLInputElement} target
   * @param {String} requestType
   * @returns void
   */
  getNavDirection = (target, requestType) => {
    if (this.requestTypes.filter == requestType) {
      return this.navDirections.firstPage;
    }
    if (target.classList.contains("first-page")) {
      return this.navDirections.firstPage;
    }
    if (target.classList.contains("last-page")) {
      return this.navDirections.lastPage;
    }
    if (target.classList.contains("prev-page")) {
      return this.navDirections.prevPage;
    }
    if (target.classList.contains("next-page")) {
      return this.navDirections.nextPage;
    }
    if (target.classList.contains("input-page")) {
      return this.navDirections.inputPage;
    }
  };
  /**
   * Updates pagination datasets
   * activates/deactivates links
   *
   * @param {Object} dataPageArgs
   * @return voids
   */
  setDataPage = (dataPageArgs) => {
    const { wrap, navDirection, page, requestType, postsTotal, pagesTotal } =
      dataPageArgs;
    const nextLink = wrap.querySelector(".next-page");
    const prevLink = wrap.querySelector(".prev-page");
    const firstLink = wrap.querySelector(".first-page");
    const lastLink = wrap.querySelector(".last-page");
    if (!nextLink && !prevLink && !lastLink && firstLink) return;
    let isFirstPage = true;
    let isLastPage = false;

    switch (navDirection) {
      case this.navDirections.firstPage:
        nextLink.dataset.page = 2;
        if (this.requestTypes.filter == requestType) {
          const allItemsNumWrapper = wrap.querySelector(".displaying-num .num");
          if (allItemsNumWrapper) {
            allItemsNumWrapper.innerText = postsTotal; // all items found num
          }
          const allPagesNumWrapper = wrap.querySelector(".of-total-pages");
          if (allPagesNumWrapper) {
            allPagesNumWrapper.innerText = pagesTotal;
          }
          lastLink.dataset.page = pagesTotal; // last page num.
        }
        this.togglePaginationLinks({
          isFirstPage,
          isLastPage,
          nextLink,
          prevLink,
          firstLink,
          lastLink,
        });
        break;
      case this.navDirections.lastPage:
        prevLink.dataset.page = page - 1;
        isFirstPage = false;
        isLastPage = true;
        this.togglePaginationLinks({
          isFirstPage,
          isLastPage,
          nextLink,
          prevLink,
          firstLink,
          lastLink,
        });
        break;
      case this.navDirections.prevPage:
        isFirstPage = false;
        isLastPage = false;
        if (page > 1) {
          prevLink.dataset.page = page - 1;
          nextLink.dataset.page = +page + 1;
        } else {
          isFirstPage = true;
          prevLink.dataset.page = 1;
          nextLink.dataset.page = 2;
        }
        this.togglePaginationLinks({
          isFirstPage,
          isLastPage,
          nextLink,
          prevLink,
          firstLink,
          lastLink,
        });
        break;
      case this.navDirections.nextPage:
        isFirstPage = false;
        isLastPage = false;
        nextLink.dataset.page = +page + 1;
        prevLink.dataset.page = +page - 1;
        if (+page >= pagesTotal) {
          isLastPage = true;
        }

        this.togglePaginationLinks({
          isFirstPage,
          isLastPage,
          nextLink,
          prevLink,
          firstLink,
          lastLink,
        });
        break;
      case this.navDirections.inputPage:
        isFirstPage = false;
        isLastPage = false;
        if (+page < pagesTotal && +page > 1) {
          // 2 : total-1
          nextLink.dataset.page = +page + 1;
          prevLink.dataset.page = +page - 1;
        } else if (page == 1) {
          isFirstPage = true;
          nextLink.dataset.page = 2;
        } else if (page == pagesTotal) {
          isLastPage = true;
          prevLink.dataset.page = pagesTotal - 1;
        }
        this.togglePaginationLinks({
          isFirstPage,
          isLastPage,
          nextLink,
          prevLink,
          firstLink,
          lastLink,
        });
        break;
    }
  };
  /**
   * Updates the paginated result
   * list wrapper's
   * postTypes dataset
   *
   *
   * @param {HTMLElement} target
   * @param {Array} postTypes
   * @return void
   */
  updatePostTypesSearch = (target, postTypes) => {
    const paginatedResultsWrapper = target.closest(".search-results-wrapper");
    if (paginatedResultsWrapper) {
      paginatedResultsWrapper.dataset.postTypes = JSON.stringify(postTypes);
    }
  };
  /**
   * Toggles(shows/hides)
   * the pagination
   * @param {String} state
   */
  togglePagination = (state) => {
    const paginationWrappers = document.querySelectorAll(
      ".bwpas-wrap .tablenav-pages"
    );
    if (paginationWrappers.length > 0) {
      paginationWrappers.forEach((pagination) => {
        if (this.paginationState.hide == state) {
          if (!pagination.classList.contains("hidden-pagination-block")) {
            pagination.classList.add("hidden-pagination-block");
          }
        } else if (this.paginationState.show == state) {
          if (pagination.classList.contains("hidden-pagination-block")) {
            pagination.classList.remove("hidden-pagination-block");
          }
        }
      });
    }
  };
  /**
   * Toggles(enables/disables)
   * the pagination links
   * @param {*} args
   */
  togglePaginationLinks = (args) => {
    const { isFirstPage, isLastPage, nextLink, prevLink, firstLink, lastLink } =
      args;
    if (isFirstPage) {
      if (!firstLink.classList.contains("disabled-page-nav"))
        firstLink.classList.add("disabled-page-nav");
      if (!prevLink.classList.contains("disabled-page-nav"))
        prevLink.classList.add("disabled-page-nav");
    } else {
      // enable the links
      if (prevLink.classList.contains("disabled-page-nav"))
        prevLink.classList.remove("disabled-page-nav");
      if (firstLink.classList.contains("disabled-page-nav"))
        firstLink.classList.remove("disabled-page-nav");
    }
    if (isLastPage) {
      if (!nextLink.classList.contains("disabled-page-nav"))
        nextLink.classList.add("disabled-page-nav");
      if (!lastLink.classList.contains("disabled-page-nav"))
        lastLink.classList.add("disabled-page-nav");
    } else {
      if (nextLink.classList.contains("disabled-page-nav"))
        nextLink.classList.remove("disabled-page-nav");
      if (lastLink.classList.contains("disabled-page-nav"))
        lastLink.classList.remove("disabled-page-nav");
    }
  };
}
