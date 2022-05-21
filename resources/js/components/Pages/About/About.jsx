import React from 'react';
import Page from "../../Page"

const About = () => {
    return (
        <Page>
            <div className="card">
                <div className={"card-header"}>
                    About
                </div>
                <div className="card-body pt-0">
                    <div style={{color: 'white'}}>
                        Bot for automatic Bitcoin trading on Binance exchange
                    </div>
                </div>
            </div>
        </Page>
    );
};

export default About;
