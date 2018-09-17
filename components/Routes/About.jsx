import React from 'react';
import PropTypes from 'prop-types';
import { mapAboutProps, mapAboutDispatch } from '../maps/Routes/About.map';
import { connect } from 'react-redux';

class AboutUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div>
                <h3>Thank You</h3>
                <p>
                    Thank you for your interest in BroncoCast. We are busy continually improving the service. Please
                    check back later for information on how you can use BroncoCast for your own organization or
                    operate your BroncoCast server.
                </p>
                <p>
                    Questions? Contact <a href="mailto:broncocast@teambroncobots.com">broncocast@teambroncobots.com</a>
                </p>
            </div>
        );
    }
}

AboutUi.propTypes = {
    init: PropTypes.func.isRequired
};

const Container = connect(mapAboutProps, mapAboutDispatch)(AboutUi);

export default Container;
