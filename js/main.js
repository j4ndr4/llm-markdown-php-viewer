// LLM Markdown Viewer JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add any interactive functionality here
    
    // Example: Expandable code blocks
    const codeBlocks = document.querySelectorAll('pre');
    codeBlocks.forEach(function(block) {
        // Add copy button to code blocks
        const copyButton = document.createElement('button');
        copyButton.className = 'copy-button';
        copyButton.innerHTML = 'Copy';
        copyButton.title = 'Copy code to clipboard';
        
        // Insert the copy button at the top of the code block
        block.insertBefore(copyButton, block.firstChild);
        
        copyButton.addEventListener('click', function() {
            const codeElement = block.querySelector('code');
            if (codeElement) {
                navigator.clipboard.writeText(codeElement.textContent)
                    .then(() => {
                        // Show temporary success message
                        copyButton.textContent = 'Copied!';
                        setTimeout(() => {
                            copyButton.textContent = 'Copy';
                        }, 2000);
                    })
                    .catch(err => {
                        console.error('Failed to copy: ', err);
                    });
            }
        });
    });
    
    // Initialize collapsible thought blocks
    const thoughtBlocks = document.querySelectorAll('.thought-block');
    thoughtBlocks.forEach(function(block) {
        // Add click handler for mobile devices
        const summary = block.querySelector('summary');
        if (summary) {
            summary.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    block.classList.toggle('open');
                }
            });
        }
    });
});