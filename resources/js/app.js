

function sumNumberOfLeads() {
    // Get all rows from the table body
    const rows = document.querySelectorAll('tbody tr');

    // Initialize the sum
    let sum = 0;
    let count = 1;

    // Regular expression to match the pattern of the desired cells
    const regex = /cell-\d+-\d+-of_applicants-\d+/;

    // Iterate through each row
    rows.forEach(row => {
        // Ensure the row has the required data-test-id attribute
        let rowId = row.getAttribute('data-test-id');
        if (rowId) {
            rowId = rowId.replace('row-', '');


            // Construct the column identifier
            const col_identifier = "cell-0-1-of_applicants-" + rowId;



            // Construct the selector
            const selector = "td[data-table-external-id='" + col_identifier + "']";

            // Select the cell that matches the selector within the row
            const numberOfLeadsCell = row.querySelector(selector);


            console.log('Row:', count, 'Row ID:', rowId, 'Column ID:', col_identifier, 'Value:', numberOfLeadsCell.innerText);
            if (numberOfLeadsCell ) {
                // Parse the inner text of the cell to an integer and add to the sum
                const numberOfLeads = parseInt(numberOfLeadsCell.innerText, 10);

                if (!isNaN(numberOfLeads)) {
                    sum += numberOfLeads;
                }
            }

            count++;
        }
    });

    return sum;
}

// Call the function and log the result
const totalLeads = sumNumberOfLeads();
console.log('Total number of leads:', totalLeads);

